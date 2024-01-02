<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helper\GeofenceHelper;
use App\Model\Unit;
use App\Helper\CronLogHelper;

class ProcessLacakSegment extends Command
{
    protected $signature = 'process:lacak-segment {unit} {start_date} {end_date}';

    protected $description = 'Process Lacak Segment BSC_01 2023-08-01 2023-08-02';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $cron_helper = new CronLogHelper;

        try {
            $unit = $this->argument('unit');
            $start_date_cron = $this->argument('start_date');
            $end_date_cron = $this->argument('end_date');
            $list_unit_table = array();
            $iteration_segment = 1;
            $final_segment = 1;
            $luasan = 0;
            $label_unit = array_count_values($list_unit_table);
            
            DB::beginTransaction();

            DB::table("lacak_".$unit)
                ->where('lokasi_kode', '!=', '')
                ->where('is_segment', 0)
                ->whereRaw("FROM_UNIXTIME(`utc_timestamp`,'%Y-%m-%d') BETWEEN '{$start_date_cron}' and '{$end_date_cron}'")
                ->orderBy('created_at') 
                ->chunk(300, function ($lokasi_kode_units) use ($unit, &$iteration_segment, &$luasan, &$final_segment) {
                    foreach ($lokasi_kode_units as $by_lokasi) {
                        $table_name = "lacak_".$unit;

                        $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);

                        $cekTable = DB::table($table_segment_label)
                            ->where('lacak_bsc_id', $by_lokasi->id)
                            ->count();
                        if ($cekTable > 0) {
                            DB::table($table_name)
                                ->where('id', $by_lokasi->id)
                                ->update([
                                    'is_segment' => true
                                ]);

                            DB::commit();
                            continue;
                        }

                        // Hitung luasan
                        $left = 0;
                        $right = 0;
                        if ($by_lokasi->pump_switch_main == 1) {
                            if ($by_lokasi->pump_switch_left == 1) {
                                $left = 18;
                            }

                            if ($by_lokasi->pump_switch_right == 1) {
                                $right = 18;
                            }
                        }

                        if ($by_lokasi->speed > 0.9) {
                            $luasan = ($by_lokasi->speed / 3.6) * ($left + $right);
                        }
                        // End hitung luasan

                        // Hitung segment
                        $segment_data = DB::table("{$table_segment_label}")->orderBy('id', 'desc')->first();
                        if ($segment_data) {
                            if ($segment_data->kode_lokasi == $by_lokasi->lokasi_kode) {
                                $final_segment = $iteration_segment;
                            } else {
                                $iteration_segment = $iteration_segment + 1;
                                $final_segment = $iteration_segment;
                            }
                        }
                        // End hitung segment

                        // Hitung overlapping
                        $overlapping_route = 0;
                        $overlapping_left = 0;
                        $overlapping_right = 0;
                        $dt = new \DateTime("@$by_lokasi->utc_timestamp");
                        $start_date = $dt->format('Y-m-d')." 00:00:00";
                        $end_date = $dt->format('Y-m-d H:i:s');
                        $new_date = $dt->format('Y-m-d');
                        // End hitung overlapping

                        // Convert epoch timestamp to DateTime object using Carbon
                        $dateTime = \Carbon\Carbon::createFromTimestamp($by_lokasi->utc_timestamp);

                        // You can format the DateTime object as per your requirements
                        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');

                        DB::insert("INSERT INTO {$table_segment_label} (
                            lacak_bsc_id, 
                            kode_lokasi, 
                            segment, 
                            overlapping_route, 
                            overlapping_left, 
                            overlapping_right,
                            luasan_m2, 
                            created_at,
                            report_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                            $by_lokasi->id,
                            $by_lokasi->lokasi_kode,
                            $final_segment,
                            $overlapping_route,
                            $overlapping_left,
                            $overlapping_right,
                            round($luasan, 2),
                            $formattedDateTime,
                            $by_lokasi->report_date
                        ]);

                        DB::table($table_name)
                            ->where('id', $by_lokasi->id)
                            ->update([
                                'is_segment' => true
                            ]);

                        DB::commit();

                        $idSegment = DB::table($table_segment_label)
                            ->where('lacak_bsc_id', $by_lokasi->id)
                            ->first();

                        $startID = $idSegment->id - 11;

                        if (0 > $startID) {
                            continue;
                        }

                        $getDataBsc = array();
                        if ($by_lokasi->speed > 0.9) {
                            $getSegment = DB::table($table_segment_label)
                                ->whereBetween('id', array(1, $startID))
                                ->where('kode_lokasi', $by_lokasi->lokasi_kode)
                                ->where('report_date', $by_lokasi->report_date)
                                ->pluck('lacak_bsc_id');

                            $geofenceHelper = new GeofenceHelper;
                            $getDataBsc = DB::table($table_name)
                                ->select('latitude', 'longitude')
                                ->where('speed', '>', 0.9)
                                ->whereIn('id', $getSegment)
                                ->get()
                                ->toArray();
                        }

                        if ($getDataBsc) {
                            $left = 0;
                            $right = 0;
                            $overlapping = $geofenceHelper->calculateOverlap($by_lokasi->latitude, $by_lokasi->longitude, $getDataBsc);

                            if ($overlapping == 1 && $by_lokasi->pump_switch_main == 1) {
                                $left = $by_lokasi->bearing > 90 ? ($by_lokasi->pump_switch_right == null ? 0 : $by_lokasi->pump_switch_right) : ($by_lokasi->pump_switch_left == null ? 0 : $by_lokasi->pump_switch_left);
                                $right = $by_lokasi->bearing > 90 ? ($by_lokasi->pump_switch_left == null ? 0 : $by_lokasi->pump_switch_left) : ($by_lokasi->pump_switch_right == null ? 0 : $by_lokasi->pump_switch_right);
                            }

                            DB::table($table_segment_label)
                                ->where('lacak_bsc_id', $by_lokasi->id)
                                ->update([
                                    'overlapping_route' => $overlapping,
                                    'overlapping_left' => $left,
                                    'overlapping_right' => $right
                                ]);

                            DB::commit();
                        }

                        $successInputCount = DB::table($table_segment_label)->count();
                        $this->info("Total successful data input to $table_segment_label: $successInputCount");
                    }
                });

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            dd($e->getMessage());
        }
    }
}

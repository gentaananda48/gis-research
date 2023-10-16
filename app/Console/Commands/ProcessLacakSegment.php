<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Helper\GeofenceHelper;
use Illuminate\Support\Facades\Redis;
use App\Model\Unit;
use App\Model\KoordinatLokasi;
use App\Model\CronLog;
use App\Helper\CronLogHelper;

class ProcessLacakSegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:lacak-segment {unit} {start_date} {end_date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Lacak Segment BSC_01 2023-08-01 2023-08-02';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $cron_helper = new CronLogHelper;
        // $cron_helper->create('process:lacak-segment', 'STARTED', 'SourceDeviceID: '.$unit);
        // $units = Unit::pluck('label')->all();
        $unit = $this->argument('unit');
        $start_date_cron = $this->argument('start_date');
        $end_date_cron = $this->argument('end_date');
        DB::beginTransaction();
        try {
            $unit = $this->argument('unit');
            // foreach($units as $source_device_id) {
                // $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($source_device_id)));
                $table_name = "lacak_".$unit;
                $list_unit_table = array();
                $iteration_segment = 1;
                $final_segment = 1;
                $luasan = 0;
                $label_unit = array_count_values($list_unit_table);
                $lokasi_kode_unit = DB::table($table_name)
                        ->select(
                            'lokasi_kode',
                            'id', 
                            'unit_label', 
                            'pump_switch_main', 
                            'pump_switch_left', 
                            'pump_switch_right', 
                            'created_at',
                            'speed',
                            'utc_timestamp',
                            'latitude',
                            'longitude',
                            'report_date',
                            'bearing'
                        )
                        ->where('lokasi_kode', '!=', '')
                        ->where('is_segment',0)
                        ->whereRaw("FROM_UNIXTIME(`utc_timestamp`,'%Y-%m-%d') BETWEEN '{$start_date_cron}' and '{$end_date_cron}'")
                        ->limit(120)
                        ->get();
                        $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
                        if ($lokasi_kode_unit) {
                            foreach ($lokasi_kode_unit as $by_lokasi ) {
                                // cek lacak segment
                                $cekTable = DB::table($table_segment_label)
                                ->where('lacak_bsc_id',$by_lokasi->id)
                                ->count();
                                if ($cekTable > 0) {
                                    DB::table($table_name)
                                    ->where('id',$by_lokasi->id)
                                    ->update([
                                        'is_segment' => true
                                    ]);

                                    DB::commit();
                                    continue;
                                }

                                // hitung luasan
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
                                    $luasan =  ($by_lokasi->speed/3.6) * ($left + $right);
                                }
                                // end hitung luasan
                                
                                // hitung segment
                                $segment_data = DB::table("{$table_segment_label}")->orderBy('id','desc')->first();
                                if ($segment_data) {
                                    if ($segment_data->kode_lokasi == $by_lokasi->lokasi_kode) {
                                        $final_segment = $iteration_segment;
                                    }else{
                                        $iteration_segment = $iteration_segment + 1;
                                        $final_segment = $iteration_segment;
                                    }
                                }
                                // end hitung segment

                                // hitung overlapping
                                    $overlapping_route = 0;
                                    $overlapping_left = 0;
                                    $overlapping_right = 0;
                                    $dt = new \DateTime("@$by_lokasi->utc_timestamp");
                                    $start_date = $dt->format('Y-m-d')." 00:00:00";
                                    $end_date = $dt->format('Y-m-d H:i:s');
                                    $new_date = $dt->format('Y-m-d');
                                    
                                // end hitung overlapping
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
                                    report_date) VALUES (?, ?, ?, ?, ?, ?, ?,?,?)", [
                                    $by_lokasi->id,
                                    $by_lokasi->lokasi_kode,
                                    $final_segment,
                                    $overlapping_route, 
                                    $overlapping_left,
                                    $overlapping_right,
                                    round($luasan,2),
                                    $formattedDateTime,
                                    $by_lokasi->report_date
                                ]);

                                DB::table($table_name)
                                    ->where('id',$by_lokasi->id)
                                    ->update([
                                        'is_segment' => true
                                    ]);

                                DB::commit();

                                $idSegment = DB::table($table_segment_label)
                                ->where('lacak_bsc_id',$by_lokasi->id)
                                ->first();

                                $startID = $idSegment->id - 11;
                                
                                if (0 > $startID) {
                                    continue;
                                }

                                $getDataBsc = array();
                                if ($by_lokasi->speed > 0.9) {
                                    $getSegment = DB::table($table_segment_label)
                                    ->whereBetween('id',array(1,$startID))
                                    ->where('kode_lokasi',$by_lokasi->lokasi_kode)
                                    ->where('report_date',$by_lokasi->report_date)
                                    ->pluck('lacak_bsc_id');

                                    $geofenceHelper = new GeofenceHelper;
                                    $getDataBsc = DB::table($table_name)
                                    ->select('latitude','longitude')
                                    ->where('speed','>',0.9)
                                    ->whereIn('id',$getSegment)
                                    ->get()
                                    ->toArray();
                                }
                                
                                
                                if ($getDataBsc) {
                                    $left = 0;
                                    $right = 0;
                                    $overlapping = $geofenceHelper->calculateOverlap($by_lokasi->latitude, $by_lokasi->longitude, $getDataBsc);
                                    // cek kondisi wing main
                                    if ($overlapping  == 1 && $by_lokasi->pump_switch_main  == 1) {
                                    $left = $by_lokasi->bearing > 90 ? ($by_lokasi->pump_switch_right == null ? 0:$by_lokasi->pump_switch_right) :($by_lokasi->pump_switch_left == null ? 0:$by_lokasi->pump_switch_left);
                                    $right = $by_lokasi->bearing > 90 ? ($by_lokasi->pump_switch_left == null ? 0:$by_lokasi->pump_switch_left):($by_lokasi->pump_switch_right == null ? 0:$by_lokasi->pump_switch_right);
                                    }
                                    // end cek kondisi

                                    DB::table($table_segment_label)
                                    ->where('lacak_bsc_id',$by_lokasi->id)
                                    ->update([
                                        'overlapping_route' => $overlapping,
                                        'overlapping_left' => $left,
                                        'overlapping_right' => $right
                                    ]);

                                    DB::commit();
                                }
                                // count data that already inputed into a table $lacak_segment_label
                                $successInputCount = DB::table($table_segment_label)->count();
                                // log info success
                                $this->info("Total successful data input to $table_segment_label: $successInputCount");
                                // end overlapping
                            }
                        }
            // }
            // end unit loop
            // $cron_helper->create('process:lacak-segment', 'FINISHED', 'SourceDeviceID: '.$unit.'. Finished Successfully');
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
            dd($e->getMessage());
            // $cron_helper->create('process:lacak-segment', 'STOPPED', 'SourceDeviceID: '.$unit.'. ERROR: '.$e->getMessage());
            // $this->info($geofenceHelper->distance(32.9697, -96.80322, 29.46786, -98.53506, "K") . " Kilometers");
        }
    }

    private function getOverlapping($table_bsc,$table_segment,$lokasi,$segment,$start,$end){
        $data = DB::select(DB::raw("SELECT A.id,lokasi_kode,segment,pump_switch_left,pump_switch_right
        FROM {$table_bsc} A
        INNER JOIN (SELECT Latitude,longitude
                    FROM {$table_bsc}
                                WHERE lokasi_kode = '{$lokasi}'
                    GROUP BY Latitude,longitude
                    HAVING COUNT(*) > 1) B
        ON A.Latitude = B.Latitude AND A.longitude = B.longitude
        INNER JOIN {$table_segment} on {$table_segment}.lacak_bsc_id = A.id
        WHERE segment = {$segment}
        and {$table_segment}.created_at BETWEEN '{$start}' and '{$end}'
        limit 10"));

        return $data;
    }
}

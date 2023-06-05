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
    protected $signature = 'process:lacak-segment {unit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Lacak Segment';

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
        $unit = $this->argument('unit');
        // $cron_helper->create('process:lacak-segment', 'STARTED', 'SourceDeviceID: '.$unit);
        $list_source_device_id = explode(',',$unit);
        DB::beginTransaction();
        try {
            foreach($list_source_device_id as $source_device_id) {
                $table_name = 'lacak_'.$source_device_id;

                // lacak segment
                $list_unit_table = [];
                $get_label = DB::table($table_name)->select('unit_label')->limit(1)->orderBy('id', 'desc')->get();

                foreach($get_label as $label) {
                    $table_name2 = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($label->unit_label)));
                    array_push($list_unit_table, $table_name2);
                }

                $iteration_segment = 0;
                $label_unit = array_count_values($list_unit_table);
                
                if (count(array_keys($label_unit)) > 0) {

                    foreach (array_keys($label_unit) as $unit_table) {
                        $lokasi_kode_unit = DB::table($unit_table)
                        ->select('lokasi_kode','id', 'unit_label', 'pump_switch_main', 'pump_switch_left', 'pump_switch_right', 'lokasi_kode', 'created_at')
                        ->where('lokasi_kode', '!=', '')
                        // ->groupBy('lokasi_kode')
                        // ->limit(10)
                        ->whereRaw("FROM_UNIXTIME(`utc_timestamp`,'%Y-%m-%d') BETWEEN '2023-05-01' and '2023-06-10'")
                        ->get();
                        $table_segment_label = str_replace("lacak_", "lacak_segment_", $unit_table);
                        foreach ($lokasi_kode_unit as $by_lokasi ) {
                            // $list_by_lokasi_kode = DB::table($unit_table)
                            //     ->select('id', 'unit_label', 'pump_switch_main', 'pump_switch_left', 'pump_switch_right', 'lokasi_kode', 'created_at')
                            //     ->where('lokasi_kode', '=',$lokasi->lokasi_kode)
                            //     // ->where('pump_switch_main', '=', 1)
                            //     // ->where('pump_switch_left', '=', 1)
                            //     // ->where('pump_switch_right', '=', 1)
                            //     // ->where('speed', '>', 0)
                            //     ->orderBy('lokasi_kode', 'DESC')
                            //     ->get();

                            // foreach ($list_by_lokasi_kode as $by_lokasi) {
                                DB::insert("INSERT INTO {$table_segment_label} (lacak_bsc_id, kode_lokasi, segment, overlapping_route, overlapping_left, overlapping_right, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                                    $by_lokasi->id,
                                    $by_lokasi->lokasi_kode,
                                    $iteration_segment++,
                                    0, 0, 0,
                                    $by_lokasi->created_at
                                ]);
                            // }

                            $this->info('Success inputting data to table segment: '.$table_segment_label);
                            // print_r("Success inputting data to table segment: {$table_segment_label}");
                        }
                    }
                }
            }
            DB::commit();
            // $cron_helper->create('process:lacak-segment', 'FINISHED', 'SourceDeviceID: '.$unit.'. Finished Successfully');
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollback(); 
            Log::error($e->getMessage());
            // $cron_helper->create('process:lacak-segment', 'STOPPED', 'SourceDeviceID: '.$unit.'. ERROR: '.$e->getMessage());
        }
    }
}

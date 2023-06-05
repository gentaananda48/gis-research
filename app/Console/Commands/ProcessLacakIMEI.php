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

class ProcessLacakIMEI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:lacak-imei {imei}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Lacak IMEI';

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
        $imei = $this->argument('imei');
        $cron_helper->create('process:lacak-imei', 'STARTED', 'SourceDeviceID: '.$imei);
        $list_source_device_id = explode(',',$imei);
        DB::beginTransaction();
        try {
            $geofenceHelper = new GeofenceHelper;
            $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT';
            $cached = Redis::get($cache_key);
            $list_koordinat_lokasi = [];
            if(isset($cached)) {
                $list_koordinat_lokasi = json_decode($cached, FALSE);
            } else {
                $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
                    ->orderBy('bagian', 'ASC')
                    ->orderBy('posnr', 'ASC')
                    ->get();
                Redis::set($cache_key, json_encode($list_koordinat_lokasi));
            }

            $list_polygon = [];
            foreach($list_koordinat_lokasi as $v){
                $idx = $v->lokasi.'_'.$v->bagian;
                if(array_key_exists($idx, $list_polygon)){
                    $list_polygon[$idx][] = $v->latd." ".$v->long;
                } else {
                    $list_polygon[$idx] = [$v->latd." ".$v->long];
                }
            }
            foreach($list_source_device_id as $source_device_id) {
                $table_name = 'lacak_'.$source_device_id;
                $list_lacak = DB::table($table_name)
                    ->where('processed', '=', 0)
                    ->whereNotNull('unit_label')
                    ->orderBy('utc_timestamp', 'ASC')
                    ->limit(500)
                    ->get();

                foreach($list_lacak as $v){
                    $lokasi_kode = $geofenceHelper->checkLocation($list_polygon, $v->latitude, $v->longitude);
                    $lokasi_kode = !empty($lokasi_kode) ? substr($lokasi_kode,0,strlen($lokasi_kode)-2) : '';
                    $table_name2 = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($v->unit_label)));
                    if($source_device_id == 860264050863753){
                        DB::insert('insert into '.$table_name2.' (latitude, longitude, speed, altitude, arm_height_left, arm_height_right, temperature_left, temperature_right, pump_switch_main, pump_switch_left, pump_switch_right, flow_meter_left, flow_meter_right, tank_level, oil, gas, homogenity, bearing, microcontroller_id, `utc_timestamp`, created_at, box_id, unit_label, source_device_id, lokasi_kode, processed, report_date, device_timestamp) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0, ?, ?) ON DUPLICATE KEY UPDATE `utc_timestamp` = ?', [$v->latitude, $v->longitude, $v->speed, $v->altitude, $v->arm_height_left, $v->arm_height_right, $v->temperature_left, $v->temperature_right, $v->pump_switch_main, $v->pump_switch_left, $v->pump_switch_right, $v->flow_meter_left, $v->flow_meter_right, $v->tank_level, $v->oil, $v->gas, $v->homogenity, $v->bearing, $v->microcontroller_id, $v->utc_timestamp, $v->box_id, $v->unit_label, $source_device_id,  $lokasi_kode, $v->report_date, $v->utc_timestamp, $v->device_timestamp]);
                    } else {
                        DB::insert('insert into '.$table_name2.' (latitude, longitude, speed, altitude, arm_height_left, arm_height_right, temperature_left, temperature_right, pump_switch_main, pump_switch_left, pump_switch_right, flow_meter_left, flow_meter_right, tank_level, oil, gas, homogenity, bearing, microcontroller_id, `utc_timestamp`, created_at, box_id, unit_label, source_device_id, lokasi_kode, processed, report_date) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0, ?) ON DUPLICATE KEY UPDATE `utc_timestamp` = ?', [$v->latitude, $v->longitude, $v->speed, $v->altitude, $v->arm_height_left, $v->arm_height_right, $v->temperature_left, $v->temperature_right, $v->pump_switch_main, $v->pump_switch_left, $v->pump_switch_right, $v->flow_meter_left, $v->flow_meter_right, $v->tank_level, $v->oil, $v->gas, $v->homogenity, $v->bearing, $v->microcontroller_id, $v->utc_timestamp, $v->box_id, $v->unit_label, $source_device_id,  $lokasi_kode, $v->report_date, $v->utc_timestamp]);
                    }

                    DB::table($table_name)->where('id', '=', $v->id)->update(['processed'=>1]);
                }

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
                        $lokasi_kode_unit = DB::table($unit_table)->select('lokasi_kode')->where('lokasi_kode', '!=', '')->groupBy('lokasi_kode')->limit(10)->get();
                        $table_segment_label = str_replace("lacak_", "lacak_segment_", $unit_table);
                        foreach ($lokasi_kode_unit as $lokasi ) {
                            $list_by_lokasi_kode = DB::table($unit_table)
                                ->select('id', 'unit_label', 'pump_switch_main', 'pump_switch_left', 'pump_switch_right', 'lokasi_kode', 'created_at')
                                ->where('lokasi_kode', '=',$lokasi->lokasi_kode)
                                // ->where('pump_switch_main', '=', 1)
                                // ->where('pump_switch_left', '=', 1)
                                // ->where('pump_switch_right', '=', 1)
                                // ->where('speed', '>', 0)
                                ->orderBy('lokasi_kode', 'DESC')
                                ->get();
                            
                            foreach ($list_by_lokasi_kode as $by_lokasi) {
                                DB::insert("INSERT INTO {$table_segment_label} (lacak_bsc_id, kode_lokasi, segment, overlapping_route, overlapping_left, overlapping_right, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                                    $by_lokasi->unit_label,
                                    $by_lokasi->lokasi_kode,
                                    $iteration_segment++,
                                    0, 0, 0,
                                    $by_lokasi->created_at
                                ]);
                            }
                            print_r("Success inputting data to table segment: {$table_segment_label}");
                        }
                    }
                }
            }
            DB::commit();
            $cron_helper->create('process:lacak-imei', 'FINISHED', 'SourceDeviceID: '.$imei.'. Finished Successfully');
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
            $cron_helper->create('process:lacak-imei', 'STOPPED', 'SourceDeviceID: '.$imei.'. ERROR: '.$e->getMessage());
        }
    }
}

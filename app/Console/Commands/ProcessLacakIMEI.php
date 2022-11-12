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
        $source_device_id = $this->argument('imei');
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
        DB::beginTransaction();
        try {
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
                //echo $table_name2.'=>'.$v->latitude.",".$v->longitude." : ".$lokasi_kode."\n";

                DB::insert('insert into '.$table_name2.' (latitude, longitude, speed, altitude, arm_height_left, arm_height_right, temperature_left, temperature_right, pump_switch_main, pump_switch_left, pump_switch_right, flow_meter_left, flow_meter_right, tank_level, oil, gas, homogenity, bearing, microcontroller_id, `utc_timestamp`, created_at, box_id, unit_label, source_device_id, lokasi_kode, processed) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 0) ON DUPLICATE KEY UPDATE `utc_timestamp` = ?', [$v->latitude, $v->longitude, $v->speed, $v->altitude, $v->arm_height_left, $v->arm_height_right, $v->temperature_left, $v->temperature_right, $v->pump_switch_main, $v->pump_switch_left, $v->pump_switch_right, $v->flow_meter_left, $v->flow_meter_right, $v->tank_level, $v->oil, $v->gas, $v->homogenity, $v->bearing, $v->microcontroller_id, $v->utc_timestamp, $v->box_id, $v->unit_label, $source_device_id,  $lokasi_kode, $v->utc_timestamp]);
                DB::table($table_name)->where('id', '=', $v->id)->update(['processed'=>1]);
            }
            //echo count($list_lacak)."\n";
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

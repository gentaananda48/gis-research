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

class ProcessSummaryOperational extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:summary-operational {tgl1} {tgl2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Summary Operational';

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
        $tgl1 = $this->argument('tgl1');
        $timestamp1 = strtotime($tgl1.' 00:00:00');
        $tgl2 = $this->argument('tgl2');
        $timestamp2 = strtotime($tgl2.' 23:59:59');
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
            $list_lacak = DB::select("CALL get_summary_operational(".$timestamp1.",".$timestamp2.")");
            foreach($list_lacak as $v){
                $lokasi_kode = $geofenceHelper->checkLocation($list_polygon, $v->latitude, $v->longitude);
                $lokasi_kode = !empty($lokasi_kode) ? substr($lokasi_kode,0,strlen($lokasi_kode)-2) : '';
                DB::insert('insert into summary_operational (`timestamp`, unit, speed, latitude, longitude, pump_switch_main, pump_switch_left, pump_switch_right, lokasi, status, creatd_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?) ON DUPLICATE KEY UPDATE `timestamp` = ? AND unit = ?', [$v->timestamp, $v->unit, $v->speed, $v->latitude, $v->longitude, $v->pump_switch_main, $v->pump_switch_left, $v->pump_switch_right, $lokasi_kode, $v->status, $v->timestamp, $v->unit]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

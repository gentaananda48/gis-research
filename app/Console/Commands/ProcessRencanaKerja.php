<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\RencanaKerja;
use App\Model\ReportRencanaKerja2;

class ProcessRencanaKerja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:rencana-kerja';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Rencana Kerja';

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
        $tgl = '2022-11-04';
        $list_unit = ['BSC - 34'];
        DB::beginTransaction();
        try {
            $list_rk = RencanaKerja::where('tgl', $tgl)->whereIn('unit_label', $list_unit)->get();
            foreach($list_rk as $rk) {
                $unit_label = trim($rk->unit_label);
                $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', $unit_label));
                $list_lacak = DB::table($table_name)
                    ->where('unit_label', $unit_label)
                    ->where('utc_timestamp', '>=', strtotime($rk->tgl.' 00:00:00'))
                    ->where('utc_timestamp', '<=', strtotime($rk->tgl.' 23:59:59'))
                    ->orderBy('utc_timestamp', 'ASC')
                    ->get();
                foreach($list_lacak as $lacak){
                    $rrk = ReportRencanaKerja2::where('unit_label', $unit_label)->where('utc_timestamp', $lacak->utc_timestamp)->first();
                    if($rrk==null){
                        $rrk = new ReportRencanaKerja2;
                        $rrk->unit_label = $unit_label;
                        $rrk->utc_timestamp = $lacak->utc_timestamp;
                        $rrk->created_at = date('Y-m-d H:i:s');
                    }
                    $rrk->rencana_kerja_id = $rk->id;
                    $rrk->tanggal = $rk->tgl;
                    $rrk->shift = $rk->shift_nama;
                    $rrk->lokasi = $rk->lokasi_kode;
                    $rrk->luas_bruto = $rk->lokasi_lsbruto;
                    $rrk->luas_netto = $rk->lokasi_lsnetto;
                    $rrk->kode_aktivitas = $rk->aktivitas_kode;
                    $rrk->nama_aktivitas = $rk->aktivitas_nama;
                    $rrk->nozzle = $rk->nozzle_nama;
                    $rrk->volume = $rk->volume;
                    $rrk->kode_unit = $rk->unit_id;
                    $rrk->nama_unit = $rk->unit_label;
                    $rrk->device_id = $rk->unit_source_device_id;
                    $rrk->operator = $rk->operator_nama;
                    $rrk->driver = $rk->driver_nama;
                    $rrk->kasie = $rk->kasie_nama;
                    $rrk->status = $rk->status_nama;
                    $rrk->jam_mulai = $rk->jam_mulai;
                    $rrk->jam_selesai = $rk->jam_selesai;

                    $rrk->latitude = $lacak->latitude;
                    $rrk->longitude = $lacak->longitude;
                    $rrk->speed = $lacak->speed;
                    $rrk->altitude = $lacak->altitude;
                    $rrk->arm_height_left = $lacak->arm_height_left;
                    $rrk->arm_height_right = $lacak->arm_height_right;
                    $rrk->temperature_left = $lacak->temperature_left;
                    $rrk->temperature_right = $lacak->temperature_right;
                    $rrk->pump_switch_main = $lacak->pump_switch_main;
                    $rrk->pump_switch_left = $lacak->pump_switch_left;
                    $rrk->pump_switch_right = $lacak->pump_switch_right;
                    $rrk->flow_meter_left = $lacak->flow_meter_left;
                    $rrk->flow_meter_right = $lacak->flow_meter_right;
                    $rrk->tank_level = $lacak->tank_level;
                    $rrk->oil = $lacak->oil;
                    $rrk->gas = $lacak->gas;
                    $rrk->homogenity = $lacak->homogenity;
                    $rrk->bearing = $lacak->bearing;
                    $rrk->microcontroller_id = $lacak->microcontroller_id;
                    $rrk->box_id = $lacak->box_id;
                    $rrk->save();

                    DB::table($table_name)->where('id', '=', $lacak->id)->update(['processed'=>1]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

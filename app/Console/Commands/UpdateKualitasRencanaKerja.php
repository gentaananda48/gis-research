<?php

namespace App\Console\Commands;

use App\Model\RencanaKerja;
use App\Model\ReportRencanaKerja;
use App\Helper\GeofenceHelper;
use App\Model\SystemConfiguration;
use App\Model\Lacak;
use App\Model\Aktivitas;
use App\Model\RencanaKerjaSummary;

use App\Model\ReportParameter;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\ReportStatus;
use App\Model\VReportRencanaKerja;
use App\Model\VReportRencanaKerja2;
use Illuminate\Support\Facades\DB;

use Illuminate\Console\Command;

class UpdateKualitasRencanaKerja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kualitas:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {
        $oldLimit = ini_get( 'memory_limit' );
        ini_set( 'memory_limit', '-1' );
        set_time_limit(0);
        $list_rk = RencanaKerja::
            //whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL AND (kualitas IS NULL OR kualitas = '')")
             whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL")
            ->orderBy('id', 'ASC')
            ->limit(100)
            ->get();
        if(count($list_rk)>0){
            $list_rp = ReportParameter::orderBy('id', 'ASC')->get();
            $list_rs = ReportStatus::get();
            foreach($list_rk AS $rk) {
                $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get()->toArray();
                $kualitas = '';
                if(count($list_rrk)>0) {
                    $aktivitas = Aktivitas::find($rk->aktivitas_id);
                    $list_realisasi = [];
                    foreach($list_rp as $rp){
                        if($rp->id!=2){
                            $list_realisasi[$rp->id] = 0;
                        }
                    }
                    foreach($list_rrk as $k=>$v){
                        foreach($list_rp as $rp){
                            if($rp->id!=2){
                                $list_realisasi[$rp->id] += $v['parameter_'.$rp->id];
                            }
                        }
                    }
                    foreach($list_rp as $rp){
                        if($rp->id==2){
                            $list_realisasi[$rp->id] = $list_rrk[0]['parameter_'.$rp->id];
                        } else {
                            $list_realisasi[$rp->id] = $list_realisasi[$rp->id] / count($list_rrk);
                        }
                    }
                    $total_poin = 0;
                    foreach($list_rp as $rp){
                        $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                            ->where('d.report_parameter_id', $rp->id)
                            ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                            ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                            ->where('report_parameter_standard.volume_id', $rk->volume_id)
                            ->orderByRaw("d.range_1*1 ASC")
                            ->get(['d.*']);
                        $standard = '';
                        foreach($list_rps AS $rps){
                            if($rps->point==1){
                                $standard = $rps->range_1.' - '.$rps->range_2;
                            }
                        }
                        $realisasi = $list_realisasi[$rp->id];
                        $nilai = 0;
                        if($rp->id==2){
                            foreach($list_rps AS $rps){
                                $dt_nilai = date('Y-m-d '.$nilai);
                                $dt_range_1 = date('Y-m-d '.$rps->range_1);
                                if($rps->range_1 > $rps->range_2) {
                                    if($dt_nilai < $dt_range_1){
                                        $dt_nilai = date('Y-m-d '.$nilai,strtotime("+1 days"));
                                    }
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2,strtotime("+1 days"));
                                } else {
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                                }
                                if($dt_range_1 <= $dt_nilai && $dt_nilai <= $dt_range_2){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        } else {
                            foreach($list_rps AS $rps){
                                if(doubleval($rps->range_1) <= $realisasi && $realisasi <= doubleval($rps->range_2)){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        }
                        $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_UNIT')->first(['value']);
                        $offline_unit = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                        if(in_array($rk->unit_id, $offline_unit)){
                            $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_BOBOT')->first(['value']);
                            $offline_bobot = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                            $bobot = !empty($offline_bobot[$rp->id-1]) ? $offline_bobot[$rp->id-1] : 0;
                        } else {
                            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                                ->where('report_parameter_id', $rp->id)
                                ->first();
                            $bobot = !empty($rpb->bobot) ? $rpb->bobot : 0;
                        }
                        $poin = $nilai * $bobot;
                        $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                            ->where('ritase', 999)
                            ->where('parameter_id', $rp->id)
                            ->first();
                        if($rks==null){
                            $rks = new RencanaKerjaSummary;
                            $rks->rk_id = $rk->id;
                            $rks->ritase = 999;
                            $rks->parameter_id = $rp->id;
                        }
                        $rks->parameter_nama = $rp->nama;
                        $rks->standard      = $standard;
                        $rks->realisasi     = $realisasi;
                        $rks->nilai         = $nilai;
                        $rks->bobot         = $bobot;
                        $rks->nilai_bobot   = $poin;
                        $rks->kualitas      = null;
                        $rks->save();
                        $total_poin += $poin;
                    }
                    $kualitas = '';
                    foreach($list_rs as $v){
                        if(doubleval($v->range_1) <= $total_poin && $total_poin <= doubleval($v->range_2)){
                            $kualitas = $v->status;
                            break;
                        }
                    }
                    $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                        ->where('ritase', 999999)
                        ->where('parameter_id', 999)
                        ->first();
                    if($rks==null){
                        $rks = new RencanaKerjaSummary;
                        $rks->rk_id = $rk->id;
                        $rks->ritase = 999999;
                        $rks->parameter_id = 999;
                        $rks->parameter_nama = 'Total';
                    }
                    $rks->standard      = null;
                    $rks->realisasi     = null;
                    $rks->nilai         = null;
                    $rks->bobot         = null;
                    $rks->nilai_bobot   = $total_poin;
                    $rks->kualitas      = $kualitas;
                    $rks->save();
                } else {
                    $kualitas = '-';
                }
                $rk->kualitas = $kualitas;
                $rk->jam_laporan2 = date('Y-m-d H:i:s');
                $rk->save();
            } 
        }
        ini_set( 'memory_limit', $oldLimit );

        $this->info('save succes '. $rk->id);

    }
}

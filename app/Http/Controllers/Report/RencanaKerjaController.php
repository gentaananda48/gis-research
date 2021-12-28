<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaSummary;
use App\Model\Lokasi;
use App\Model\Shift;
use App\Model\Aktivitas;
use App\Model\Unit;
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Model\Status;
use App\Model\ReportStatus;
use App\Model\KoordinatLokasi;
use App\Model\Lacak;
use App\Model\ReportRencanaKerja;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\VReportRencanaKerja;
use App\Center\GridCenter;
use App\Transformer\ReportRencanaKerjaTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportRencanaKerjaExport;
use App\Helper\GeofenceHelper;

class RencanaKerjaController extends Controller {
    public function index() {
        $list_shift = [];
        $list_lokasi = [];
        $list_aktivitas = [];
        $list_unit = [];
        $list_nozzle = [];
        $list_volume = [];
        $list_status = [];
        $list_report_status = [];
        $res = Shift::get(['id', 'nama']);
        foreach($res AS $v){
            $list_shift[$v->id] = $v->nama;
        }
        $res = Lokasi::get(['kode', 'nama']);
        foreach($res AS $v){
            $list_lokasi[$v->kode] = $v->nama;
        }
        $res = Aktivitas::get(['kode', 'nama']);
        foreach($res AS $v){
            $list_aktivitas[$v->kode] = $v->nama;
        }
        $res = Unit::get(['id', 'label']);
        foreach($res AS $v){
            $list_unit[$v->id] = $v->label;
        }
        $res = Nozzle::get(['id', 'nama']);
        foreach($res AS $v){
            $list_nozzle[$v->id] = $v->nama;
        }
        $res = VolumeAir::get(['volume']);
        foreach($res AS $v){
            $list_volume[$v->volume] = $v->volume;
        }
        $res = Status::where('proses', 'RENCANA_KERJA')->get(['id', 'nama']);
        foreach($res AS $v){
            $list_status[$v->id] = $v->nama;
        }
        $res = ReportStatus::get(['id', 'status']);
        foreach($res AS $v){
            $list_report_status[$v->status] = $v->status;
        }
        return view('report.rencana_kerja.index', [
            'list_shift'        => $list_shift,
            'list_lokasi'       => $list_lokasi,
            'list_aktivitas'    => $list_aktivitas,
            'list_unit'         => $list_unit,
            'list_nozzle'       => $list_nozzle,
            'list_volume'       => $list_volume,
            'list_status'       => $list_status,
            'list_report_status'    => $list_report_status
        ]);
    }

    public function get_list(Request $request){
        $user = $this->guard()->user();
        $kasie_id = $user->id;
        $query = RencanaKerja::where('status_id', 4)
            //->where('kasie_id', $kasie_id)
            ->whereIn('lokasi_grup', explode(',', $user->area));
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d', strtotime($tgl[1]));
            $query->whereBetween('tgl', [$tgl_1, $tgl_2]);
        }
        if(isset($request->shift)){
            $query->whereIn('shift_id', $request->shift);
        }
        if(isset($request->lokasi)){
            $query->whereIn('lokasi_kode', $request->lokasi);
        }
        if(isset($request->aktivitas)){
            $query->whereIn('aktivitas_kode', $request->aktivitas);
        }
        if(isset($request->unit)){
            $query->whereIn('unit_id', $request->unit);
        }
        if(isset($request->nozzle)){
            $query->whereIn('nozzle_id', $request->nozzle);
        }
        if(isset($request->volume)){
            $query->whereIn('volume', $request->volume);
        }
        if(isset($request->status)){
            $query->whereIn('status_id', $request->status);
        }
        if(isset($request->kualitas)){
            $query->whereIn('kualitas', $request->kualitas);
        }
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportRencanaKerjaTransformer()));
        exit;
    }

    public function export(Request $request){
        $user = $this->guard()->user();
        return Excel::download(new ReportRencanaKerjaExport($request, $user->id), 'report_rencana_kerja.xlsx');
    }

    public function summary($id) {
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $unit = Unit::find($rk->unit_id);
        $list = KoordinatLokasi::where('lokasi', $rk->lokasi_kode)
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $list_lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp', 'din_1', 'din_2', 'din_3']);

        $list_rrk = VReportRencanaKerja::where('rencana_kerja_id', $id)->get();
        $kualitas = '';
        if(count($list_rrk)>0) {
            $aktivitas = Aktivitas::find($rk->aktivitas_id);
            $kecepatan_operasi = 0;
            $waktu_spray_per_ritase = 0;
            foreach($list_rrk as $v){
                $kecepatan_operasi += $v->kecepatan_operasi;
                $waktu_spray_per_ritase += $v->waktu_spray_per_ritase;
            }
            $golden_time = $list_rrk[0]->golden_time;
            $kecepatan_operasi = $kecepatan_operasi / count($list_rrk);
            $waktu_spray_per_ritase = $waktu_spray_per_ritase / count($list_rrk);

            $poin_kecepatan_operasi = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 1)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if(doubleval($rps->range_1) <= $kecepatan_operasi && $kecepatan_operasi <= doubleval($rps->range_2)){
                    $poin_kecepatan_operasi = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 1)
                ->first();
            $poin_kecepatan_operasi = !empty($rpb->bobot) ? $poin_kecepatan_operasi * $rpb->bobot : 0;

            $poin_golden_time = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 2)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                $dt_golden_time = date('Y-m-d '.$golden_time);
                $dt_range_1 = date('Y-m-d '.$rps->range_1);
                if($rps->range_1 > $rps->range_2) {
                    if($dt_golden_time < $dt_range_1){
                        $dt_golden_time = date('Y-m-d '.$golden_time,strtotime("+1 days"));
                    }
                    $dt_range_2 = date('Y-m-d '.$rps->range_2,strtotime("+1 days"));
                } else {
                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                }
                if($dt_range_1 <= $dt_golden_time && $dt_golden_time <= $dt_range_2){
                    $poin_golden_time = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 2)
                ->first();
            $poin_golden_time = !empty($rpb->bobot) ? $poin_golden_time * $rpb->bobot : 0;

            $poin_waktu_spray_per_ritase = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 3)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if(doubleval($rps->range_1) <= $waktu_spray_per_ritase && $waktu_spray_per_ritase <= doubleval($rps->range_2)){
                    $poin_waktu_spray_per_ritase = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 3)
                ->first();
            $poin_waktu_spray_per_ritase = !empty($rpb->bobot) ? $poin_waktu_spray_per_ritase * $rpb->bobot : 0;

            $total_poin = $poin_kecepatan_operasi+$poin_golden_time+$poin_waktu_spray_per_ritase;
            $list_rs = ReportStatus::get();
            $kualitas = '';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $total_poin && $total_poin <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }

            $summary = (object) [
                'ritase' => $list_rrk,
                'rata2' => (object) [
                    'kecepatan_operasi'         => $kecepatan_operasi,
                    'golden_time'               => $golden_time,
                    'waktu_spray_per_ritase'    => $waktu_spray_per_ritase
                ],
                'poin' => (object) [
                    'kecepatan_operasi'         => $poin_kecepatan_operasi,
                    'golden_time'               => $poin_golden_time,
                    'waktu_spray_per_ritase'    => $poin_waktu_spray_per_ritase,
                    'total_poin'                => $total_poin
                ],
                'kualitas'                      => $kualitas
            ];
        } else {
            $kualitas = '-';
            $summary = (object) [
                'ritase' => [],
                'rata2' => (object) [
                    'kecepatan_operasi'         => '',
                    'golden_time'               => '',
                    'waktu_spray_per_ritase'    => ''
                ],
                'poin' => (object) [
                    'kecepatan_operasi'         => '',
                    'golden_time'               => '',
                    'waktu_spray_per_ritase'    => '',
                    'total_poin'                => ''
                ],
                'kualitas'                      => $kualitas
            ];
        }
        if($rk->kualitas!=$kualitas){
            $rk->kualitas = $kualitas;
            $rk->save();
        }
        return view('report.rencana_kerja.summary', [
            'rk'            => $rk, 
            'summary'       => $summary,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi)
        ]);
    }

    public function playback(Request $request, $id) {
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($rk->unit_id);
        $list_interval = [];
        for($i=1; $i<=10; $i++){
            $list_interval[$i*100] = ($i/10).' Detik';
        }
        $list = KoordinatLokasi::orderBy('lokasi', 'ASC')
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $geofenceHelper = new GeofenceHelper;
        $durasi = strtotime($jam_selesai) - strtotime($jam_mulai) + 1;
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp', 'din_1', 'din_2', 'din_3']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return view('report.rencana_kerja.playback', [
            'rk'            => $rk, 
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
    }

    protected function guard(){
        return Auth::guard('web');
    }
}

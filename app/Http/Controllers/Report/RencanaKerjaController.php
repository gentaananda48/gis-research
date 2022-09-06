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
use App\Model\Lacak2;
use App\Model\ReportRencanaKerja;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\VReportRencanaKerja;
use App\Model\VReportRencanaKerja2;
use App\Center\GridCenter;
use App\Transformer\ReportRencanaKerjaTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportRencanaKerjaExport;
use App\Helper\GeofenceHelper;
use Illuminate\Support\Facades\Redis;

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
        $list_lokasi = [];
        foreach($list_koordinat_lokasi as $v){
            if($v->lokasi==$rk->lokasi_kode){
                $idx = $v->lokasi.'_'.$v->bagian;
                if(array_key_exists($idx, $list_lokasi)){
                    $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
                } else {
                    $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
                }
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$unit->source_device_id;
        $cache_key = $rk->tgl >= date('Y-m-d') ? $cache_key.'_'.$jam_selesai : $cache_key.'_'.$rk->tgl;
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_mulai) : strtotime($rk->tgl.' 00:00:00');
            $timestamp_2 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($rk->tgl.' 23:59:59');
            if($rk->tgl>='2022-03-15') {
                $list_lacak = Lacak2::where('ident', $unit->source_device_id)
                    ->where('timestamp', '>=', $timestamp_1)
                    ->where('timestamp', '<=', $timestamp_2)
                    ->orderBy('timestamp', 'ASC')
                    ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'din_1', 'din_2', 'din_3', 'payload_text', 'timestamp']);
            } else {
                $list_lacak = Lacak::where('ident', $unit->source_device_id)
                    ->where('timestamp', '>=', $timestamp_1)
                    ->where('timestamp', '<=', $timestamp_2)
                    ->orderBy('timestamp', 'ASC')
                    ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'din_1', 'din_2', 'din_3', 'payload_text', 'timestamp']);
            }
            Redis::set($cache_key, json_encode($list_lacak), 'EX', 86400);
        }
        $list_lacak2 = [];
        foreach($list_lacak as $v){
            if(strtotime($jam_mulai) <= doubleval($v->timestamp) && doubleval($v->timestamp) <= strtotime($jam_selesai)) {
                $list_lacak2[] = $v;
            }
        }
        $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $id)->get()->toArray();
        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->get();
        $header = [];
        $rata2 = [];
        $poin = [];
        $kualitas = '-';
        foreach($list_rks as $rks) {
            if($rks->ritase==999){
                $header[$rks->parameter_id] = $rks->parameter_nama;
                $rata2[$rks->parameter_id] = $rks->realisasi;
                $poin[$rks->parameter_id] = $rks->nilai_bobot;
            } else if($rks->ritase==999999){
                $poin[999] = $rks->nilai_bobot;
                $kualitas = $rks->kualitas;
            }
        }
        $summary = (object) [
            'header'    => $header,
            'ritase'    => $list_rrk,
            'rata2'     => $rata2,
            'poin'      => $poin,
            'kualitas'  => $kualitas
        ];
        return view('report.rencana_kerja.summary', [
            'rk'            => $rk, 
            'summary'       => $summary,
            'list_lacak'    => json_encode($list_lacak2),
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
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list_koordinat_lokasi as $v){
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

        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$unit->source_device_id;
        $cache_key = $rk->tgl >= date('Y-m-d') ? $cache_key.'_'.$jam_selesai : $cache_key.'_'.$rk->tgl;
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_mulai) : strtotime($rk->tgl.' 00:00:00');
            $timestamp_2 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($rk->tgl.' 23:59:59');
            if($rk->tgl>='2022-03-15') {
                $list_lacak = Lacak2::where('ident', $unit->source_device_id)
                    ->where('timestamp', '>=', $timestamp_1)
                    ->where('timestamp', '<=', $timestamp_2)
                    ->orderBy('timestamp', 'ASC')
                    ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'din_1', 'din_2', 'din_3', 'payload_text', 'timestamp']);
            } else {
                $list_lacak = Lacak::where('ident', $unit->source_device_id)
                    ->where('timestamp', '>=', $timestamp_1)
                    ->where('timestamp', '<=', $timestamp_2)
                    ->orderBy('timestamp', 'ASC')
                    ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'din_1', 'din_2', 'din_3', 'payload_text', 'timestamp']);
            }
            Redis::set($cache_key, json_encode($list_lacak), 'EX', 86400);
        }
        $list_lacak2 = [];
        foreach($list_lacak as $v){
            if(strtotime($jam_mulai) <= doubleval($v->timestamp) && doubleval($v->timestamp) <= strtotime($jam_selesai)) {
                $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
                $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
                $v->progress_time = doubleval($v->timestamp) - strtotime($jam_mulai);
                $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
                $v->timestamp_2 = date('H:i:s', $v->timestamp);
                $list_lacak2[] = $v;
            }
        }
        return view('report.rencana_kerja.playback', [
            'rk'            => $rk, 
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak2),
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

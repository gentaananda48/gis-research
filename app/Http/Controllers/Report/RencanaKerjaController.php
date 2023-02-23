<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaSummary;
use App\Model\SystemConfiguration;
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
use App\Model\ReportParameterStandardDetail;
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
    public function index(Request $request) {
        $user = $this->guard()->user();
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
        if(empty($request->tgl)){
            $tgl = date('m/01/Y').' - '.date('m/t/Y');
            return redirect()->route('report.rencana_kerja', ['tgl' => $tgl]);
        }
        return view('report.rencana_kerja.index', [
            'list_shift'        => $list_shift,
            'list_lokasi'       => $list_lokasi,
            'list_aktivitas'    => $list_aktivitas,
            'list_unit'         => $list_unit,
            'list_nozzle'       => $list_nozzle,
            'list_volume'       => $list_volume,
            'list_status'       => $list_status,
            'list_report_status'    => $list_report_status,
            'tgl'               => $request->tgl,
            'shift'             => $request->shift,
            'lokasi'            => $request->lokasi,
            'aktivitas'         => $request->aktivitas,
            'unit'              => $request->unit,
            'nozzle'            => $request->nozzle,
            'volume'            => $request->volume,
            'status'            => $request->status,
            'kualitas'          => $request->kualitas
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
        $prev_memory_limit = ini_get('memory_limit');
        $prev_max_execution_time = ini_get('max_execution_time');
        ini_set('memory_limit', '-1' );
        ini_set('max_execution_time', 0);
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $unit = Unit::find($rk->unit_id);
        // $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT';
        // $cached = Redis::get($cache_key);
        // $list_koordinat_lokasi = [];
        // if(isset($cached)) {
        //     $list_koordinat_lokasi = json_decode($cached, FALSE);
        // } else {
        //     $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
        //         ->orderBy('bagian', 'ASC')
        //         ->orderBy('posnr', 'ASC')
        //         ->get();
        //     Redis::set($cache_key, json_encode($list_koordinat_lokasi));
        // }

        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT_'.$rk->lokasi_kode;
        $cached = Redis::get($cache_key);
        $list_koordinat_lokasi = [];
        if(isset($cached)) {
            $list_koordinat_lokasi = json_decode($cached, FALSE);
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::where('lokasi', $rk->lokasi_kode)
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

        $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
        $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$rk->unit_source_device_id;
        if(in_array($rk->unit_source_device_id, $offline_units)){
            $cache_key = env('APP_CODE').':UNIT:PLAYBACK2_'.$rk->unit_source_device_id;
        }
        $tgl = $rk->tgl;
        if($tgl >= date('Y-m-d')) {
            $redis_scan_result = Redis::scan(0, 'match', $cache_key.'_'.$tgl.'*');
            $cache_key = $cache_key.'_'.$jam_selesai;
            if(count($redis_scan_result[1])>0){
                rsort($redis_scan_result[1]);
                $last_key = $redis_scan_result[1][0];
                if($cache_key<$last_key){
                    $cache_key = $last_key;
                }
                foreach($redis_scan_result[1] as $key){
                    if($key!=$cache_key){
                        Redis::del($key);
                    }
                }
            }
        } else {
            $cache_key = $cache_key.'_'.$tgl;
        }
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = strtotime($rk->tgl.' 00:00:00');
            $timestamp_2 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($rk->tgl.' 23:59:59');
            //
            if(in_array($rk->unit_source_device_id, $offline_units)){
                $table_name = 'lacak_'.$rk->unit_source_device_id;
                $list_lacak = DB::table($table_name)
                    ->where('report_date', $tgl)
                    //->where('utc_timestamp', '>=', $timestamp_1)
                    //->where('utc_timestamp', '<=', $timestamp_2)
                    ->orderBy('utc_timestamp', 'ASC')
                    ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, pump_switch_right, pump_switch_left, pump_switch_main, arm_height_right, arm_height_left, `utc_timestamp` AS timestamp")
                    ->get();
            } else {
                if($rk->tgl>='2022-03-15') {
                    $list_lacak = Lacak2::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                } else {
                    $list_lacak = Lacak::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                }
            }
            //
            Redis::set($cache_key, json_encode($list_lacak), 'EX', 2592000);
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
                $rata2[$rks->parameter_id] = $rks->parameter_id!=2 ? number_format($rks->realisasi,2) : $rks->realisasi;
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
        $standard = [
            'speed_range_1'             => -999999,
            'speed_range_2'             => 999999,
            'arm_height_left_range_1'   => -999999,
            'arm_height_left_range_2'   => 999999,
            'arm_height_right_range_1'  => -999999,
            'arm_height_right_range_2'  => 999999
        ];
        $rpsd_speed = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 1)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_speed!=null){
            $standard['speed_range_1'] = doubleval($rpsd_speed->range_1);
            $standard['speed_range_2'] = doubleval($rpsd_speed->range_2);
        }
        $rpsd_arm_height_left = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 4)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_arm_height_left!=null){
            $standard['arm_height_left_range_1'] = doubleval($rpsd_arm_height_left->range_1);
            $standard['arm_height_left_range_2'] = doubleval($rpsd_arm_height_left->range_2);
        }
        $rpsd_arm_height_right = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 5)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_arm_height_right!=null){
            $standard['arm_height_right_range_1'] = doubleval($rpsd_arm_height_right->range_1);
            $standard['arm_height_right_range_2'] = doubleval($rpsd_arm_height_right->range_2);
        }
        $list_percentage = DB::select("CALL get_report_percentage_ritase(".$id.",".$standard['speed_range_1'].",".$standard['speed_range_2'].",".$standard['arm_height_right_range_1'].",".$standard['arm_height_right_range_2'].",".$standard['arm_height_left_range_1'].",".$standard['arm_height_left_range_2'].")");
        ini_set('memory_limit', $prev_memory_limit);
        ini_set('max_execution_time', $prev_max_execution_time);
        return view('report.rencana_kerja.summary', [
            'rk'            => $rk, 
            'summary'       => $summary,
            'list_lacak'    => json_encode($list_lacak2),
            'list_lokasi'   => json_encode($list_lokasi),
            'list_percentage'   => $list_percentage
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
        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT_'.$rk->lokasi_kode;
        $cached = Redis::get($cache_key);
        $list_koordinat_lokasi = [];
        if(isset($cached)) {
            $list_koordinat_lokasi = json_decode($cached, FALSE);
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::where('lokasi', $rk->lokasi_kode)
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            Redis::set($cache_key, json_encode($list_koordinat_lokasi));
        }
        $list_lokasi = [];
        foreach($list_koordinat_lokasi as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $durasi = strtotime($jam_selesai) - strtotime($jam_mulai) + 1;

        $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
        $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$rk->unit_source_device_id;
        if(in_array($rk->unit_source_device_id, $offline_units)){
            $cache_key = env('APP_CODE').':UNIT:PLAYBACK2_'.$rk->unit_source_device_id;
        }
        $tgl = $rk->tgl;
        if($tgl >= date('Y-m-d')) {
            $redis_scan_result = Redis::scan(0, 'match', $cache_key.'_'.$tgl.'*');
            $cache_key = $cache_key.'_'.$jam_selesai;
            if(count($redis_scan_result[1])>0){
                rsort($redis_scan_result[1]);
                $last_key = $redis_scan_result[1][0];
                if($cache_key<$last_key){
                    $cache_key = $last_key;
                }
                foreach($redis_scan_result[1] as $key){
                    if($key!=$cache_key){
                        Redis::del($key);
                    }
                }
            }
        } else {
            $cache_key = $cache_key.'_'.$tgl;
        }
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = strtotime($tgl.' 00:00:00');
            $timestamp_2 = $tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($tgl.' 23:59:59');

            //
            if(in_array($rk->unit_source_device_id, $offline_units)){
                $table_name = 'lacak_'.$rk->unit_source_device_id;
                $list_lacak = DB::table($table_name)
                    ->where('report_date', $tgl)
                    //->where('utc_timestamp', '>=', $timestamp_1)
                    //->where('utc_timestamp', '<=', $timestamp_2)
                    ->orderBy('utc_timestamp', 'ASC')
                    ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, pump_switch_right, pump_switch_left, pump_switch_main, arm_height_right, arm_height_left, `utc_timestamp` AS timestamp")
                    ->get();
            } else {
                if($rk->tgl>='2022-03-15') {
                    $list_lacak = Lacak2::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                } else {
                    $list_lacak = Lacak::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                }
            }
            //

            Redis::set($cache_key, json_encode($list_lacak), 'EX', 2592000);
        }
        $list_by_timestamp = [];
        foreach($list_lacak as $v){
            if(strtotime($jam_mulai) <= doubleval($v->timestamp) && doubleval($v->timestamp) <= strtotime($jam_selesai)) {
                $list_by_timestamp[$v->timestamp] = $v;
            }
        }
        $position_latitude_0 = 0;
        $position_longitude_0 = 0;
        foreach($list_by_timestamp as $v){
            $position_latitude_0 = $v->position_latitude;
            $position_longitude_0 = $v->position_longitude;
            break;
        }
        $start = strtotime($jam_mulai);
        $finish = strtotime($jam_selesai);
        $duration = $finish - $start;
        $interval = 1000;
        $last = (object) [
            'timestamp'             => 0, 
            'position_latitude'     => $position_latitude_0, 
            'position_longitude'    => $position_longitude_0, 
            'position_altitude'     => 0, 
            'position_direction'    => 0, 
            'position_speed'        => 0, 
            'pump_switch_right'     => 0, 
            'pump_switch_left'      => 0, 
            'pump_switch_main'      => 0, 
            'arm_height_right'      => 0, 
            'arm_height_left'       => 0
        ];
        $list_lacak2 = [];
        for($i=$start; $i<=$finish; $i++){
            if(!empty($list_by_timestamp[$i])) {
                $obj = $list_by_timestamp[$i];
                $last = $list_by_timestamp[$i];
            } else {
                $obj = $last;
                $obj->timestamp = $i;
            }
            $obj->timestamp_2 = date('H:i:s', $obj->timestamp);
            $obj->progress_time = doubleval($obj->timestamp) - $start;
            $obj->progress_time_pers = ($obj->progress_time / $duration) * 100;
            $list_lacak2[] = (object) [
                'position_latitude'         => $obj->position_latitude, 
                'position_longitude'        => $obj->position_longitude, 
                'position_altitude'         => !empty($obj->position_altitude) ? $obj->position_altitude : 0, 
                'position_direction'        => !empty($obj->position_direction) ? $obj->position_direction : 0, 
                'position_speed'            => !empty($obj->position_speed) ? $obj->position_speed : 0, 
                'pump_switch_right'         => !empty($obj->pump_switch_right) ? $obj->pump_switch_right : 0, 
                'pump_switch_left'          => !empty($obj->pump_switch_left) ? $obj->pump_switch_left : 0, 
                'pump_switch_main'          => !empty($obj->pump_switch_main) ? $obj->pump_switch_main: 0, 
                'arm_height_right'          => !empty($obj->arm_height_right) ? $obj->arm_height_right : 0, 
                'arm_height_left'           => !empty($obj->arm_height_left) ? $obj->arm_height_left : 0, 
                'timestamp'                 => $obj->timestamp,
                'timestamp_2'               => $obj->timestamp_2, 
                'progress_time'             => $obj->progress_time, 
                'progress_time_pers'        => $obj->progress_time_pers
            ];
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
                $rata2[$rks->parameter_id] = $rks->parameter_id!=2 ? number_format($rks->realisasi,2) : $rks->realisasi;
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

        $standard = [
            'speed_range_1'             => -999999,
            'speed_range_2'             => 999999,
            'arm_height_left_range_1'   => -999999,
            'arm_height_left_range_2'   => 999999,
            'arm_height_right_range_1'  => -999999,
            'arm_height_right_range_2'  => 999999
        ];
        $rpsd_speed = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 1)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_speed!=null){
            $standard['speed_range_1'] = doubleval($rpsd_speed->range_1);
            $standard['speed_range_2'] = doubleval($rpsd_speed->range_2);
        }
        $rpsd_arm_height_left = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 4)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_arm_height_left!=null){
            $standard['arm_height_left_range_1'] = doubleval($rpsd_arm_height_left->range_1);
            $standard['arm_height_left_range_2'] = doubleval($rpsd_arm_height_left->range_2);
        }
        $rpsd_arm_height_right = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
            ->where('rps.aktivitas_id', $rk->aktivitas_id)
            ->where('rps.nozzle_id', $rk->nozzle_id)
            ->where('rps.volume_id', $rk->volume_id)
            ->where('report_parameter_standard_detail.report_parameter_id', 5)
            ->where('report_parameter_standard_detail.point', 1)
            ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);
        if($rpsd_arm_height_right!=null){
            $standard['arm_height_right_range_1'] = doubleval($rpsd_arm_height_right->range_1);
            $standard['arm_height_right_range_2'] = doubleval($rpsd_arm_height_right->range_2);
        }
        $list_percentage = DB::select("CALL get_report_percentage_ritase(".$id.",".$standard['speed_range_1'].",".$standard['speed_range_2'].",".$standard['arm_height_right_range_1'].",".$standard['arm_height_right_range_2'].",".$standard['arm_height_left_range_1'].",".$standard['arm_height_left_range_2'].")");
        return view('report.rencana_kerja.playback', [
            'rk'            => $rk, 
            'summary'       => $summary,
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak2),
            'list_lokasi'   => json_encode($list_lokasi),
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi,
            'standard'      => json_encode((object) $standard),
            'list_percentage'   => $list_percentage
        ]);
    }

    protected function guard(){
        return Auth::guard('web');
    }
}

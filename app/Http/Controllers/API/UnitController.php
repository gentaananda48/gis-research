<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Model\Unit;
use App\Model\Lacak;
use App\Model\KoordinatLokasi;
use App\Helper\GeofenceHelper;

class UnitController extends Controller {

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['playback_view']]);
    }

    public function list(Request $request){
        $list = Unit::orderBy('label', 'ASC')->get();
        $list_unit = [];
        foreach($list AS $v){
            $lacak = Lacak::where('ident', $v->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first();
            $v->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
            $v->position_latitude        = $lacak != null ? $lacak->position_longitude : 0;
            $v->movement_status          = $lacak != null ? $lacak->movement_status : 0;
            $v->movement_status_desc     = !empty($v->movement_status) ? 'moving': 'stopped';
            $v->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
            $v->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
            $v->position_direction       = $lacak != null ? $lacak->position_direction : 0;
            $v->position_speed           = $lacak != null ? $lacak->position_speed : 0;
            $v->nozzle_kanan             = $lacak != null && $lacak->ain_1 != null ? $lacak->ain_1 : 0;
            $v->nozzle_kiri              = $lacak != null && $lacak->ain_2 != null ? $lacak->ain_2 : 0;
            $list_unit[] = $v;
        }
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
        $unit = Unit::find($request->id);
        if($unit==null){
            return response()->json([
                'status'    => false, 
                'message'   => 'Unit ID: '.$request->id.' not found', 
                'data'      => null
            ]);
        }

        $lacak = Lacak::where('ident', $unit->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first();
        $unit->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        $unit->position_longitude       = $lacak != null ? $lacak->position_longitude : 0;
        $unit->movement_status          = $lacak != null ? $lacak->movement_status : 0;
        $unit->movement_status_desc     = !empty($unit->movement_status) ? 'moving': 'stopped';
        $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_1) ? 'On' : 'Off';
        $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_2) ? 'On' : 'Off';

        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $unit->lokasi = $geofenceHelper->checkLocation($list_polygon, $unit->position_latitude, $unit->position_longitude);
        $unit->lokasi = !empty($unit->lokasi) ? substr($unit->lokasi,0,strlen($unit->lokasi)-2) : '';
        return response()->json([
            'status'    => true, 
            'message'   => '', 
            'data'      => $unit
        ]);
    }

    public function playback(Request $request) {
        $id = !empty($request->id) ? $request->id : 0;
        $tgl = !empty($request->tgl) ? $request->tgl : date('Y-m-d');
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($id);
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
        $jam_mulai = $tgl.' 00:00:00';
        $jam_selesai = $tgl.' 23:59:59';
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($jam_mulai);
            $v->progress_time_pers = ($v->progress_time / 86400) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return response()->json([
            'status'    => true, 
            'message'   => '', 
            'data'      => [
                'unit'          => $unit,
                'list_lacak'    => $list_lacak,
                'list_lokasi'   => $list_lokasi,
                'tgl'           => $tgl,
                'list_interval' => $list_interval,
                'interval'      => $interval
            ]
        ]);
    }

    public function playback_view(Request $request) {
        $id = !empty($request->id) ? $request->id :0;
        $tgl = !empty($request->tgl) ? $request->tgl : date('Y-m-d');
        $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '00:00:00';
        $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : '23:59:00';
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($id);
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
        $tgl_jam_mulai = $tgl.' '.$jam_mulai;
        $tgl_jam_selesai = $tgl.' '.$jam_selesai;
        $durasi = strtotime($tgl_jam_selesai) - strtotime($tgl_jam_mulai) + 1;
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($tgl_jam_mulai))
            ->where('timestamp', '<=', strtotime($tgl_jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'din_1', 'din_2', 'din_3', 'timestamp']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($tgl_jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return view('api.unit.playback', [
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'tgl'           => $tgl,
            'jam_mulai'     => $jam_mulai,
            'jam_selesai'   => $jam_selesai,
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
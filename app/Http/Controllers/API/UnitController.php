<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Model\Unit;
use App\Model\VUnit;
use App\Model\Lacak;
use App\Model\SystemConfiguration;
use App\Model\KoordinatLokasi;
use App\Helper\GeofenceHelper;

class UnitController extends Controller {

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['tracking_view', 'playback_view', 'offline_data']]);
    }

    public function list(Request $request){
        $user = $this->guard()->user();
        $list = Unit::whereIn('pg', explode(',', $user->area))->orderBy('label', 'ASC')->get();
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
            $v->nozzle_kanan             = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_1) ? 'On' : 'Off';
            $v->nozzle_kiri              = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_2) ? 'On' : 'Off';
            $list_unit[] = $v;
        }
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list_unit
        ]);
    }

    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = VUnit::where('updated_at', '>', $updated_at)->get();
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
        $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_1) ? 'On' : 'Off';
        $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_2) ? 'On' : 'Off';

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

    public function tracking_view(Request $request) {
        $id = !empty($request->id) ? $request->id :0;
        $unit = Unit::find($id);
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
        }
        $list_lokasi = array_values($list_lokasi);
        return view('api.unit.tracking', [
            'list_lokasi'   => json_encode($list_lokasi),
            'unit'          => $unit
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

    public function offline_data() {
        $geoloc = [
            ['latitude' => -4.796459, 'longitude' => 105.302609],
            ['latitude' => -4.796395, 'longitude' => 105.302775],
            ['latitude' => -4.796379, 'longitude' => 105.302877],
            ['latitude' => -4.796406, 'longitude' => 105.303011],
            ['latitude' => -4.796374, 'longitude' => 105.303118],
            ['latitude' => -4.796347, 'longitude' => 105.303252],
            ['latitude' => -4.796326, 'longitude' => 105.303376],
            ['latitude' => -4.796272, 'longitude' => 105.303488],
            ['latitude' => -4.796229, 'longitude' => 105.303622],
            ['latitude' => -4.796192, 'longitude' => 105.3038],
            ['latitude' => -4.796133, 'longitude' => 105.303955],
            ['latitude' => -4.796096, 'longitude' => 105.304159],
            ['latitude' => -4.796037, 'longitude' => 105.304336],
            ['latitude' => -4.79601, 'longitude' => 105.304475],
            ['latitude' => -4.795968, 'longitude' => 105.304615],
            ['latitude' => -4.795946, 'longitude' => 105.304776],
            ['latitude' => -4.795898, 'longitude' => 105.304915],
            ['latitude' => -4.795839, 'longitude' => 105.305098],
            ['latitude' => -4.795791, 'longitude' => 105.305264],
            ['latitude' => -4.795754, 'longitude' => 105.305446],
            ['latitude' => -4.795695, 'longitude' => 105.305597],
            ['latitude' => -4.795631, 'longitude' => 105.305768],
            ['latitude' => -4.795615, 'longitude' => 105.305918],
            ['latitude' => -4.795556, 'longitude' => 105.306069],
            ['latitude' => -4.795476, 'longitude' => 105.306208],
            ['latitude' => -4.795465, 'longitude' => 105.306391],
            ['latitude' => -4.795422, 'longitude' => 105.306514],
            ['latitude' => -4.795321, 'longitude' => 105.306519],
            ['latitude' => -4.795251, 'longitude' => 105.306476],
            ['latitude' => -4.795155, 'longitude' => 105.306423],
            ['latitude' => -4.795176, 'longitude' => 105.306273]
        ];
        $sysconf = SystemConfiguration::where('code', 'OFFLINE_DATA_IDX')->first();
        $i = intval($sysconf->value);
        if($i>=count($geoloc)) {
            $i = 0;
        }
        $flow_meter_right = rand(0,50);
        $flow_meter_left = rand(0,50);
        $data = [
            'id'                    => "1000000071fc7625",
            'latitude'              => $geoloc[$i]['latitude'],
            'longitude'             => $geoloc[$i]['longitude'],
            'altitude'              => 100,
            'speed'                 => rand(0,10),
            'arm_height_left'       => rand(0,150),
            'arm_height_right'      => rand(0,150),
            'temperature_right'     => rand(20,60),
            'temperature_left'      => rand(20,60),
            'pump_switch_main'      => true,
            'pump_switch_left'      => $flow_meter_left > 0,
            'pump_switch_right'     => $flow_meter_left > 0,
            'flow_meter_right'      => $flow_meter_right,
            'flow_meter_left'       => $flow_meter_left,
            'tank_level'            => rand(0,6),
            'oil'                   => rand(0,6),
            'gas'                   => rand(0,6),
            'homogenity'            => rand(0,6),
            'utc_timestamp'         => strtotime(gmdate("Y-m-d\TH:i:s\Z"))
        ];
        $i++;
        $sysconf->value = ''.$i;
        $sysconf->save();
        return response()->json($data);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
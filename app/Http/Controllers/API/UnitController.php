<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Model\Unit;
use App\Model\Lacak;
use App\Helper\GeofenceHelper;

class UnitController extends Controller {

    public function __construct() {
        $this->middleware('auth:api', []);
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
        $unit->movement_status_desc     = !empty($v->movement_status) ? 'moving': 'stopped';
        $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        $unit->nozzle_kanan             = $lacak != null && $lacak->ain_1 != null ? $lacak->ain_1 : 0;
        $unit->nozzle_kiri              = $lacak != null && $lacak->ain_2 != null ? $lacak->ain_2 : 0;

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

    public function guard(){
        return Auth::guard('api');
    }
}
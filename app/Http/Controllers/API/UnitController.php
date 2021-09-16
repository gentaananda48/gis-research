<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\RencanaKerja;

class UnitController extends Controller {
    protected $base_url = 'https://api.lacak.io';
    protected $hash = '375f851d60cb30450125d5414c6b76c7';

    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        try {
            $list_unit = Unit::get(['lacak_id']);
            $list_lacak_id = [];
            foreach($list_unit AS $v){
                $list_lacak_id[] = $v->lacak_id;
            }
            $trackers = '['.join(',',$list_lacak_id).']';
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/get_states', [
                'form_params' => [
                    'hash'      => $this->hash,
                    'trackers'  => $trackers
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->states AS $k=>$v) {
                $unit = Unit::where('lacak_id', $k)->first();
                if($unit!=null){
                    $unit->gps_updated      = $v->gps->updated;
                    $unit->gps_signal_level = $v->gps->signal_level;
                    $unit->gps_location_lat = $v->gps->location->lat;
                    $unit->gps_location_lng = $v->gps->location->lng;
                    $unit->gps_heading      = $v->gps->heading;
                    $unit->gps_speed        = $v->gps->speed;
                    $unit->gps_alt          = $v->gps->alt;
                    $unit->movement_status  = $v->movement_status;
                    $unit->save();
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $list = Unit::get();
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
        try {
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/get_state', [
                'form_params' => [
                    'hash'          => $this->hash,
                    'tracker_id'    => $unit->lacak_id
                ]
            ]);
            $body = json_decode($res->getBody());
            $unit->gps_updated = $body->state->gps->updated;
            $unit->gps_signal_level = $body->state->gps->signal_level;
            $unit->gps_location_lat = $body->state->gps->location->lat;
            $unit->gps_location_lng = $body->state->gps->location->lng;
            $unit->gps_heading = $body->state->gps->heading;
            $unit->gps_speed = $body->state->gps->speed;
            $unit->gps_alt = $body->state->gps->alt;
            $unit->movement_status = $body->state->movement_status;
            $unit->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        try {
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/get_readings', [
                'form_params' => [
                    'hash'          => $this->hash,
                    'tracker_id'    => $unit->lacak_id
                ]
            ]);
            $water_pressure_kanan = null;
            $water_pressure_kiri = null;
            $body = json_decode($res->getBody());
            foreach($body->inputs AS $k=>$v) {
                if($v->name=='analog_1') {
                    $water_pressure_kanan = $v->value;
                } else if($v->name=='analog_2') {
                    $water_pressure_kiri = $v->value;
                }
            }
            $unit->water_pressure_kanan = $water_pressure_kanan;
            $unit->water_pressure_kiri = $water_pressure_kiri;
            $unit->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        $rk = RencanaKerja::join('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->where('unit_id', $unit->id)
            ->where('status_id', 2)
            ->first(['l.kode AS lokasi_kode']);
        $unit->lokasi = $rk == null ? null : $rk->lokasi_kode;
        $unit->save();
        return response()->json([
            'status'    => true, 
            'message'   => '', 
            'data'      => $unit
        ]);
    }

    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Unit::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
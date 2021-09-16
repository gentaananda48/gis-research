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

class UnitController extends Controller {
    protected $base_url = 'https://api.lacak.io';
    protected $hash = '375f851d60cb30450125d5414c6b76c7';

    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        $list = Unit::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
        $unit = Unit::find($request->id);
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
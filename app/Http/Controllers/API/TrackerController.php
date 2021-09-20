<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\Tracker;

class TrackerController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
    	$trackers = explode(',', $request->trackers);
        $list = Tracker::whereIn('tracker_id', $trackers)
        	->whereBetween('updated', [$request->from, $request->to])
        	->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function create(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	    	$request = (object) array_change_key_case(json_decode(json_encode($request->all()), TRUE), CASE_LOWER);
	    	$list = $request->list;
            foreach($list as $k=>$v){
                $v = (object) array_change_key_case($v, CASE_LOWER);
                $tracking = Tracker::where('tracker_id', $v->tracker_id)->where('updated', $v->updated)->first();
                if($tracking == null){
                    $tracking = new Tracker;
	                $tracking->tracker_id 	= $v->tracker_id;
	                $tracking->updated 		= $v->updated;
                }
                $tracking->label   			= $v->label;
                $tracking->lokasi  			= $v->lokasi;
                $tracking->latitude  		= $v->latitude;
                $tracking->longitude  		= $v->longitude;
                $tracking->speed  			= $v->speed;
                $tracking->signal_level  	= $v->signal_level;
                $tracking->heading  		= $v->heading;
                $tracking->movement_status  = $v->movement_status;
                $tracking->nozzle_kanan  	= $v->nozzle_kanan;
                $tracking->nozzle_kiri  	= $v->nozzle_kiri;
                $tracking->ketinggian_kanan = $v->ketinggian_kanan;
                $tracking->ketinggian_kiri 	= $v->ketinggian_kiri;
                $tracking->save();
            }     
	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Created successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

    public function guard(){
        return Auth::guard('api');
    }
}
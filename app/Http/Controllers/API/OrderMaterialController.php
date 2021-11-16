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
use App\Model\User;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaBahan;
use App\Model\RencanaKerjaLog;
use App\Model\Unit;
use App\Model\Status;
use App\Model\Aktivitas;
use App\Model\Lokasi;
use App\Model\Bahan;


class OrderMaterialController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

	public function list(Request $request){
		$user = $this->guard()->user();
        $list = RencanaKerja::where('tgl', $request->tgl)
        	->where('kasie_id', $user->id)
            ->orderBy('tgl', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list2(Request $request){
    	$user = $this->guard()->user();
        $list = RencanaKerja::where('tgl', $request->tgl)
        	->where('mixing_operator_id', $user->id)
            ->orderBy('tgl', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list3(Request $request){
    	$user = $this->guard()->user();
        $list = RencanaKerja::where('tgl', $request->tgl)
        	->where('operator_id', $user->id)
            ->orderBy('tgl', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
        $order = RencanaKerja::find($request->id);
        $bahan = RencanaKerjaBahan::where('rk_id', $request->id)
        	->get(['id', 'bahan_kode', 'bahan_nama', 'qty', 'uom']);
       	$data = [
       		'order'	=> $order,
       		'bahan'	=> $bahan
       	];
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $data
        ]);
    }

	public function start(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk 					= RencanaKerja::find($request->id);
	      	$status_id_lama 		= $rk->status_id;
	      	$status_nama_lama 		= $rk->status_nama;
	      	$status 				= Status::find(6);
	      	$rk->om_status_id 		= $status->id;
	      	$rk->om_status_nama 	= $status->nama;
	      	$rk->om_status_urutan 	= $status->urutan;
	      	$rk->om_status_color 	= $status->color;
	      	$rk->save();

	      	$rkl 					= new RencanaKerjaLog;
	      	$rkl->rk_id				= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $status->id;
	      	$rkl->status_nama 		= $status->nama;
			$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->event 			= 'Mulai Mixing';
	      	$rkl->catatan 			= '';	      	
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Started successfully', 
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

	public function cancel(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk 					= RencanaKerja::find($request->id);
	      	$status_id_lama 		= $rk->status_id;
	      	$status_nama_lama 		= $rk->status_nama;
	      	$status 	 			= Status::find(7);
	      	$rk->om_status_id 		= $status->id;
	      	$rk->om_status_nama 	= $status->nama;
	      	$rk->om_status_urutan 	= $status->urutan;
	      	$rk->om_status_color 	= $status->color;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id	= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $status->id;
	      	$rkl->status_nama 		= $status->nama;
			$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->event 			= 'Batal Mixing';
	      	$rkl->catatan 			= '';	      	
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Cancelled successfully', 
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
	public function finish(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk 		 			= RencanaKerja::find($request->id);
	      	$status_id_lama 		= $rk->status_id;
	      	$status_nama_lama 		= $rk->status_nama;
	      	$status 	 			= Status::find(8);
	      	$rk->om_status_id 		= $status->id;
	      	$rk->om_status_nama 	= $status->nama;
	      	$rk->om_status_urutan 	= $status->urutan;
	      	$rk->om_status_color 	= $status->color;
			$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id	= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $status->id;
	      	$rkl->status_nama 		= $status->nama;
			$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->event 			= 'Selesai Mixing';
	      	$rkl->catatan 			= '';	      	
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Finished successfully', 
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
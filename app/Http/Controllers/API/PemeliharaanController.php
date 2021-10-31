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
use App\Model\Pemeliharaan;
use App\Model\PemeliharaanLog;
use App\Model\Unit;
use App\Model\Status;


class PemeliharaanController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

	public function list(Request $request){
        $list = Pemeliharaan::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }
	public function show(Request $id) {
        $data = Pemeliharaan::find($id);
        return response()->json([
			'status'    => true, 
            'message'   => 'success', 
			'data' => $data
		]);

    }
    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Pemeliharaan::where('updated_at', '>', $updated_at)->get();
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
	    	$post = $request->all();
	    	$pemeliharaan 					= new Pemeliharaan();
	    	$pemeliharaan->tanggal  		= isset($post["tanggal"]) ? $post["tanggal"] : null;
			$pemeliharaan->unit_id  		= isset($post["unit_id"]) ? $post["unit_id"] : null;
			$unit 							= Unit::find($request->unit_id);
			$pemeliharaan->unit_label 		= $unit->label;
			$pemeliharaan->perbaikan 		= isset($post["perbaikan"]) ? $post["perbaikan"] : null;
			$pemeliharaan->laporan_driver  	= isset($post["laporan_driver"]) ? $post["laporan_driver"] : null;
			$pemeliharaan->kasie_id 		= $user->id;
			$pemeliharaan->kasie_nama 		= $user->name;
			$status 						= Status::find(9);
			$pemeliharaan->status_id 	 	= $status->id;
			$pemeliharaan->status_nama 	 	= $status->nama;
			$pemeliharaan->save();

			$log_pemeliharaan = new PemeliharaanLog;
	      	$log_pemeliharaan->pemeliharaan_id	= $pemeliharaan->id;
	      	$log_pemeliharaan->jam 				= date('Y-m-d H:i:s');
	      	$log_pemeliharaan->user_id 			= $user->id;
	      	$log_pemeliharaan->user_nama 	 	= $user->name;
	      	$log_pemeliharaan->status_id 		= $pemeliharaan->status_id;
	      	$log_pemeliharaan->status_nama 		= $pemeliharaan->status_nama;
			$log_pemeliharaan->status_id_lama 	= 0;
	      	$log_pemeliharaan->status_nama_lama = '';
	      	$log_pemeliharaan->event 			= 'Create';
	      	$log_pemeliharaan->catatan 			= '';	      	
	      	$log_pemeliharaan->save();

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

	public function start_maintenance(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$pemeliharaan 					= Pemeliharaan::find($request->id);
	      	$status_id_lama 				= $pemeliharaan->status_id;
	      	$status_nama_lama 				= $pemeliharaan->status_nama;
	      	$status 	 					= Status::find(10);
	      	$pemeliharaan->status_id 		= $status->id;
	      	$pemeliharaan->status_nama 		= $status->nama;
	      	$pemeliharaan->teknisi_id 		= $request->teknisi_id;
			$teknisi 						= User::find($request->teknisi_id);  
		 	$pemeliharaan->teknisi_nama 	= $teknisi->name;
			$pemeliharaan->tanggal_mulai	= date('Y-m-d H:i:s');
	      	$pemeliharaan->save();

	      	$log_pemeliharaan = new PemeliharaanLog;
	      	$log_pemeliharaan->pemeliharaan_id	= $pemeliharaan->id;
	      	$log_pemeliharaan->jam 				= date('Y-m-d H:i:s');
	      	$log_pemeliharaan->user_id 			= $user->id;
	      	$log_pemeliharaan->user_nama 	 	= $user->name;
	      	$log_pemeliharaan->status_id 		= $status->id;
	      	$log_pemeliharaan->status_nama 		= $status->nama;
			$log_pemeliharaan->status_id_lama 	= $status_id_lama;
	      	$log_pemeliharaan->status_nama_lama = $status_nama_lama;
	      	$log_pemeliharaan->event 			= 'Start';
	      	$log_pemeliharaan->catatan 			= '';	      	
	      	$log_pemeliharaan->save();

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

	public function finish_maintenance(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$pemeliharaan 					= Pemeliharaan::find($request->id);
	      	$status_id_lama 				= $pemeliharaan->status_id;
	      	$status_nama_lama 				= $pemeliharaan->status_nama;
	      	$status 	 					= Status::find(12);
	      	$pemeliharaan->status_id 		= $status->id;
	      	$pemeliharaan->status_nama 		= $status->nama;
	      	$pemeliharaan->tanggal_selesai 	= date('Y-m-d H:i:s');
	      	$pemeliharaan->catatan_teknisi	= $request->catatan_teknisi;
			$pemeliharaan->save();

	      	$log_pemeliharaan = new PemeliharaanLog;
	      	$log_pemeliharaan->pemeliharaan_id	= $pemeliharaan->id;
	      	$log_pemeliharaan->jam 				= date('Y-m-d H:i:s');
	      	$log_pemeliharaan->user_id 			= $user->id;
	      	$log_pemeliharaan->user_nama 	 	= $user->name;
	      	$log_pemeliharaan->status_id 		= $status->id;
	      	$log_pemeliharaan->status_nama 		= $status->nama;
			$log_pemeliharaan->status_id_lama 	= $status_id_lama;
	      	$log_pemeliharaan->status_nama_lama = $status_nama_lama;
	      	$log_pemeliharaan->event 			= 'Finish';
	      	$log_pemeliharaan->catatan 			= $pemeliharaan->catatan_teknisi;	      	
	      	$log_pemeliharaan->save();

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
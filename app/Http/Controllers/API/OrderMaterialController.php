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
use App\Model\OrderMaterial;
use App\Model\OrderMaterialBahan;
use App\Model\OrderMaterialLog;
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
        $list = OrderMaterial::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = OrderMaterial::where('updated_at', '>', $updated_at)->get();
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
	    	$order_material = new OrderMaterial();
	    	$order_material->tanggal  = isset($post["tanggal"]) ? $post["tanggal"] : null;
			$order_material->unit_id  = isset($post["unit_id"]) ? $post["unit_id"] : null;
			$unit = Unit::find($request->unit_id);
			$order_material->unit_label = $unit->label;
			$order_material->operator_id = isset($post["operator_id"]) ? $post["operator_id"] : null;
			$operator = User::find($request->operator_id);
			$order_material->operator_nama =$operator->name;
			$order_material->aktivitas_id = isset($post["aktivitas_id"]) ? $post["aktivitas_id"] : null;
			$aktivitas = Aktivitas::find($request->aktivitas_id);
			$order_material->aktivitas_kode = $aktivitas->kode;
			$order_material->aktivitas_nama = $aktivitas->nama;
			$order_material->lokasi_id = isset($post["lokasi_id"]) ? $post["lokasi_id"] : null;
			$lokasi = Lokasi::find($request->lokasi_id);	
			$order_material->lokasi_kode = $lokasi->kode;
			$order_material->lokasi_nama = $lokasi->nama;
			$order_material->kasie_id = $user->id;
			$order_material->kasie_nama = $user->name;
			$order_material->ritase = isset($post["ritase"]) ? $post["ritase"] : null;;
			$status = Status::find(1);
			$order_material->status = $status->nama;
			$order_material->save();
			foreach($request->bahan as $k=>$v){
				$om_bahan = new OrderMaterialBahan;
				$om_bahan->order_material_id = $order_material->id;
				$om_bahan->unit_label = $order_material->unit_label;
				$om_bahan->bahan_id = $v["bahan_id"];
				$bahan = Bahan::find($v["bahan_id"]);
				$om_bahan->bahan_kode = $bahan->kode;
				$om_bahan->bahan_nama = $bahan->nama;
				$om_bahan->qty = $v["qty"];
				$om_bahan->uom = $v["uom"];
				$om_bahan->save();
			}

			$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id = $order_material->id;
	      	$log_order_material->jam 			 = date('Y-m-d H:i:s');
	      	$log_order_material->user_id 		 = $user->id;
	      	$log_order_material->user_nama 	 	 = $user->name;
	      	$log_order_material->status_id 		 = $status->id;
	      	$log_order_material->status_nama 	 = $status->nama;
			$log_order_material->status_id_lama  = 0;
	      	$log_order_material->status_nama_lama = '';
	      	$log_order_material->event 			= 'Create';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

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

	public function start_order_material(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$order_material = OrderMaterial::find($request->id);
	      	$status_nama_lama 	= $order_material->status;
	      	$status 	 		= Status::find(5);
	      	$order_material->status 	= $status->nama;
	      	$order_material->save();

	      	$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 	= $user->name;
	      	$log_order_material->status_id 		= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= 1;
	      	$log_order_material->status_nama_lama = $status_nama_lama;
	      	$log_order_material->event 			= 'Update';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
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

	public function cancel_order_material(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$order_material = OrderMaterial::find($request->id);
	      	$status_nama_lama 	= $order_material->status;
	      	$status 	 		= Status::find(6);
	      	$order_material->status 	= $status->nama;
	      	$order_material->save();

	      	$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 	= $user->name;
	      	$log_order_material->status_id 		= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= 5;
	      	$log_order_material->status_nama_lama = $status_nama_lama;
	      	$log_order_material->event 			= 'Update';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
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
	public function finish_order_material(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$order_material = OrderMaterial::find($request->id);
	      	$status_nama_lama 	= $order_material->status;
	      	$status 	 		= Status::find(4);
	      	$order_material->status 	= $status->nama;
	      	// $order_material->catatan_teknisi	= $request->catatan_teknisi;
			$order_material->save();

	      	$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 	= $user->name;
	      	$log_order_material->status_id 		= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= 5;
	      	$log_order_material->status_nama_lama = $status_nama_lama;
	      	$log_order_material->event 			= 'Update';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
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
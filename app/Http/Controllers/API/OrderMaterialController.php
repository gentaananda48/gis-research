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
use App\Model\RencanaKerja;
use App\Model\Bahan;


class OrderMaterialController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

	public function list(Request $request){
        $list = OrderMaterial::where('kasie_id', $request->user_id)
            ->orderBy('tanggal', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list2(Request $request){
        $list = OrderMaterial::where('mixing_operator_id', $request->user_id)
            ->orderBy('tanggal', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list3(Request $request){
        $list = OrderMaterial::where('operator_id', $request->user_id)
            ->orderBy('tanggal', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
        $order = OrderMaterial::find($request->id);
        $bahan = OrderMaterialBahan::where('order_material_id', $request->id)->get(['id', 'bahan_kode', 'bahan_nama', 'qty', 'uom']);
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

    public function form_create(Request $request){
		$user = $this->guard()->user();
        $list_rk  = RencanaKerja::get();
        $list_bahan  = RencanaKerja::get();
        $list_operator 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_MIXING_OPERATOR')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
        $data = [
            'list_rk' 		=> $list_rk, 
            'list_operator'	=> $list_operator,
            'list_bahan' 	=> $list_bahan
        ];
		return response()->json([
        	'status' 	=> true, 
        	'message' 	=> '', 
        	'data' 		=> $data
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
	    	$rencana_kerja 					= RencanaKerja::find($request->rk_id);
	    	$order_material 				= new OrderMaterial();
	    	$order_material->rk_id  		= $request->rk_id;
	    	$order_material->tanggal  		= $request->tanggal;
			$order_material->unit_id  		= $rencana_kerja->unit_id;
			$order_material->unit_label 	= $rencana_kerja->unit_label;
			$order_material->aktivitas_id 	= $rencana_kerja->aktivitas_id;
			$order_material->aktivitas_kode = $rencana_kerja->aktivitas_kode;
			$order_material->aktivitas_nama = $rencana_kerja->aktivitas_nama;
			$order_material->lokasi_id 		= $rencana_kerja->lokasi_id;
			$order_material->lokasi_kode 	= $rencana_kerja->lokasi_kode;
			$order_material->lokasi_nama 	= $rencana_kerja->lokasi_nama;
			$order_material->kasie_id 		= $user->id;
			$order_material->kasie_nama 	= $user->name;
			$order_material->ritase 		= $request->ritase;
			$order_material->operator_id 	= $request->operator_id;
			$operator 						= User::find($request->operator_id);
			$order_material->operator_nama 	= $operator->name;
			$status 						= Status::find(5);
			$order_material->status_id 		= $status->id;
			$order_material->status_nama 	= $status->nama;
			$order_material->save();
			foreach($request->bahan as $k=>$v){
				$om_bahan 						= new OrderMaterialBahan;
				$om_bahan->order_material_id 	= $order_material->id;
				$om_bahan->unit_label 			= $order_material->unit_label;
				$om_bahan->bahan_id 			= $v["bahan_id"];
				$bahan 							= Bahan::find($v["bahan_id"]);
				$om_bahan->bahan_kode 			= $bahan->kode;
				$om_bahan->bahan_nama 			= $bahan->nama;
				$om_bahan->qty 					= $v["qty"];
				$om_bahan->uom 					= $bahan->uom;
				$om_bahan->save();
			}

			$log_order_material 					= new OrderMaterialLog;
	      	$log_order_material->order_material_id 	= $order_material->id;
	      	$log_order_material->jam 			 	= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 		 	= $user->id;
	      	$log_order_material->user_nama 	 	 	= $user->name;
	      	$log_order_material->status_id 		 	= $status->id;
	      	$log_order_material->status_nama 	 	= $status->nama;
			$log_order_material->status_id_lama  	= 0;
	      	$log_order_material->status_nama_lama 	= '';
	      	$log_order_material->event 				= 'Create';
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
	      	$order_material 				= OrderMaterial::find($request->id);
	      	$status_id_lama 				= $order_material->status_id;
	      	$status_nama_lama 				= $order_material->status_nama;
	      	$status 	 					= Status::find(6);
	      	$order_material->status_id 		= $status->id;
	      	$order_material->status_nama 	= $status->nama;
	      	$order_material->status_urutan 	= $status->urutan;
	      	$order_material->status_color 	= $status->color;
	      	$order_material->save();

	      	$log_order_material 					= new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 		= $user->name;
	      	$log_order_material->status_id 			= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= $status_id_lama;
	      	$log_order_material->status_nama_lama 	= $status_nama_lama;
	      	$log_order_material->event 				= 'Start';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

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

	public function cancel_order_material(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$order_material 				= OrderMaterial::find($request->id);
	      	$status_id_lama  				= $order_material->status_id;
	      	$status_nama_lama 	 			= $order_material->status_nama;
	      	$status 	 					= Status::find(7);
	      	$order_material->status_id 		= $status->id;
	      	$order_material->status_nama 	= $status->nama;
	      	$order_material->status_urutan 	= $status->urutan;
	      	$order_material->status_color 	= $status->color;
	      	$order_material->save();

	      	$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 		= $user->name;
	      	$log_order_material->status_id 			= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= $status_id_lama;
	      	$log_order_material->status_nama_lama 	= $status_nama_lama;
	      	$log_order_material->event 				= 'Cancel';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

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
	public function finish_order_material(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$order_material 		 		= OrderMaterial::find($request->id);
	      	$status_id_lama 		 		= $order_material->status_id;
	      	$status_nama_lama 		 		= $order_material->status_nama;
	      	$status 	 					= Status::find(8);
	      	$order_material->status_id 		= $status->id;
	      	$order_material->status_nama 	= $status->nama;
	      	$order_material->status_urutan 	= $status->urutan;
	      	$order_material->status_color 	= $status->color;
			$order_material->save();

	      	$log_order_material = new OrderMaterialLog;
	      	$log_order_material->order_material_id	= $order_material->id;
	      	$log_order_material->jam 				= date('Y-m-d H:i:s');
	      	$log_order_material->user_id 			= $user->id;
	      	$log_order_material->user_nama 	 		= $user->name;
	      	$log_order_material->status_id 			= $status->id;
	      	$log_order_material->status_nama 		= $status->nama;
			$log_order_material->status_id_lama 	= $status_id_lama;
	      	$log_order_material->status_nama_lama 	= $status_nama_lama;
	      	$log_order_material->event 				= 'Finish';
	      	$log_order_material->catatan 			= '';	      	
	      	$log_order_material->save();

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
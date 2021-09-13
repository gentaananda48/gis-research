<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaLog;
use App\Model\Shift;
use App\Model\Lokasi;
use App\Model\Aktivitas;
use App\Model\Unit;
use App\Model\User;

class RencanaKerjaController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        $list =RencanaKerja::select(['rencana_kerja.*', 's.nama AS shift_nama', 'l.kode AS lokasi_kode', 'l.nama AS lokasi_nama', 'a.kode AS aktivitas_kode', 'a.nama AS aktivitas_nama', 'u.kode AS unit_kode', 'u.nama AS unit_nama', 'o.name AS operator_nama', 'd.name AS driver_nama', 'k.name AS kasie_nama', 's2.nama AS status_nama', 's2.color AS status_color'])
            ->leftJoin('shift AS s', 's.id', '=', 'rencana_kerja.shift_id')
            ->leftJoin('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->leftJoin('aktivitas AS a', 'a.id', '=', 'rencana_kerja.aktivitas_id')
            ->leftJoin('unit as u', 'u.id', '=', 'rencana_kerja.unit_id')
            ->leftJoin('users AS o', 'o.id', '=', 'rencana_kerja.operator_id')
            ->leftJoin('users AS d', 'd.id', '=', 'rencana_kerja.driver_id')
            ->leftJoin('users AS k', 'k.id', '=', 'rencana_kerja.kasie_id')
            ->leftJoin('status AS s2', 's2.id', '=', 'rencana_kerja.status_id')
            ->orderBy('tgl', 'DESC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list2(Request $request){
        $list =RencanaKerja::select(['rencana_kerja.*', 's.nama AS shift_nama', 'l.kode AS lokasi_kode', 'l.nama AS lokasi_nama', 'a.kode AS aktivitas_kode', 'a.nama AS aktivitas_nama', 'u.kode AS unit_kode', 'u.nama AS unit_nama', 'o.name AS operator_nama', 'd.name AS driver_nama', 'k.name AS kasie_nama', 's2.nama AS status_nama', 's2.color AS status_color'])
            ->leftJoin('shift AS s', 's.id', '=', 'rencana_kerja.shift_id')
            ->leftJoin('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->leftJoin('aktivitas AS a', 'a.id', '=', 'rencana_kerja.aktivitas_id')
            ->leftJoin('unit as u', 'u.id', '=', 'rencana_kerja.unit_id')
            ->leftJoin('users AS o', 'o.id', '=', 'rencana_kerja.operator_id')
            ->leftJoin('users AS d', 'd.id', '=', 'rencana_kerja.driver_id')
            ->leftJoin('users AS k', 'k.id', '=', 'rencana_kerja.kasie_id')
            ->leftJoin('status AS s2', 's2.id', '=', 'rencana_kerja.status_id')
            ->where('rencana_kerja.tgl', $request->tgl)
            ->orderBy('rencana_kerja.unit_id', 'ASC')
            ->orderBy('s2.urutan', 'DESC')
            ->get();
        $list3 = [];
        foreach($list as $v){
        	if(array_key_exists($v->unit_kode, $list3)){
        		$list3[$v->unit_kode]['list'][] = $v;
        	} else {
        		$list3[$v->unit_kode] = ['unit_kode' => $v->unit_kode, 'list' => [$v]];
        	}
        }
        $list3 = array_values($list3);
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list3
        ]);
    }

    public function list3(Request $request){
        $list =RencanaKerja::select(['rencana_kerja.*', 's.nama AS shift_nama', 'l.kode AS lokasi_kode', 'l.nama AS lokasi_nama', 'a.kode AS aktivitas_kode', 'a.nama AS aktivitas_nama', 'u.kode AS unit_kode', 'u.nama AS unit_nama', 'o.name AS operator_nama', 'd.name AS driver_nama', 'k.name AS kasie_nama', 's2.nama AS status_nama', 's2.color AS status_color'])
            ->leftJoin('shift AS s', 's.id', '=', 'rencana_kerja.shift_id')
            ->leftJoin('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->leftJoin('aktivitas AS a', 'a.id', '=', 'rencana_kerja.aktivitas_id')
            ->leftJoin('unit as u', 'u.id', '=', 'rencana_kerja.unit_id')
            ->leftJoin('users AS o', 'o.id', '=', 'rencana_kerja.operator_id')
            ->leftJoin('users AS d', 'd.id', '=', 'rencana_kerja.driver_id')
            ->leftJoin('users AS k', 'k.id', '=', 'rencana_kerja.kasie_id')
            ->leftJoin('status AS s2', 's2.id', '=', 'rencana_kerja.status_id')
            ->where('rencana_kerja.tgl', $request->tgl)
            ->where('rencana_kerja.operator_id', $request->operator_id)
            ->orderBy('rencana_kerja.shift_id', 'ASC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
    	$rk = RencanaKerja::select(['rencana_kerja.*', 's.nama AS shift_nama', 'l.kode AS lokasi_kode', 'l.nama AS lokasi_nama', 'a.kode AS aktivitas_kode', 'a.nama AS aktivitas_nama', 'u.kode AS unit_kode', 'u.nama AS unit_nama', 'o.name AS operator_nama', 'd.name AS driver_nama', 'k.name AS kasie_nama', 's2.nama AS status_nama', 's2.color AS status_color'])
            ->leftJoin('shift AS s', 's.id', '=', 'rencana_kerja.shift_id')
            ->leftJoin('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->leftJoin('aktivitas AS a', 'a.id', '=', 'rencana_kerja.aktivitas_id')
            ->leftJoin('unit as u', 'u.id', '=', 'rencana_kerja.unit_id')
            ->leftJoin('users AS o', 'o.id', '=', 'rencana_kerja.operator_id')
            ->leftJoin('users AS d', 'd.id', '=', 'rencana_kerja.driver_id')
            ->leftJoin('users AS k', 'k.id', '=', 'rencana_kerja.kasie_id')
            ->leftJoin('status AS s2', 's2.id', '=', 'rencana_kerja.status_id')
            ->where('rencana_kerja.id', $request->id)
            ->first();

		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> $rk
    	]);
	} 

	public function get_master_data(Request $request){
		$list_shift 	= Shift::get(['id', 'nama']);
		$list_lokasi 	= Lokasi::get(['id', 'kode', 'nama', 'lsbruto', 'lsnetto']);
		$list_aktivitas = Aktivitas::get(['id', 'kode', 'nama']);
		$list_unit 		= Unit::get(['id', 'kode', 'nama']);
		$list_operator 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')->where('r.code', 'MBL_SPRAY_OPERATOR')->get(['users.id', 'users.name AS nama']);
		$list_driver 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')->where('r.code', 'MBL_SPRAY_DRIVER')->get(['users.id', 'users.name AS nama']);
		$data = [
			'list_shift'		=> $list_shift,
			'list_lokasi'		=> $list_lokasi,
			'list_aktivitas'	=> $list_aktivitas,
			'list_unit'			=> $list_unit,
			'list_operator' 	=> $list_operator,
			'list_driver' 		=> $list_driver
		];
		return response()->json([
        	'status' 	=> true, 
        	'message' 	=> 'Submitted successfully', 
        	'data' 		=> $data
      	]);
	}

    // Create
  	public function create(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = new RencanaKerja;
	      	$rk->tgl 			= $request->tgl;
	      	$rk->shift_id 		= $request->shift_id;
	      	$rk->lokasi_id 		= $request->lokasi_id;
	      	$rk->lokasi_lsbruto = $request->lokasi_lsbruto;
	      	$rk->lokasi_lsnetto = $request->lokasi_lsnetto;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$rk->unit_id  		= $request->unit_id;
	      	$rk->operator_id 	= $request->operator_id;
	      	$rk->driver_id 		= $request->driver_id;
	      	$rk->kasie_id  		= $user->id;
	      	$rk->status_id 		= 1;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->event 			= 'Create';
	      	$rkl->catatan 			= '';
	      	$rkl->status_id_lama 	= 0;
	      	$rkl->save();

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

	// Update
  	public function update(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$rk->tgl 			= $request->tgl;
	      	$rk->shift_id 		= $request->shift_id;
	      	$rk->lokasi_id 		= $request->lokasi_id;
	      	$rk->lokasi_lsbruto = $request->lokasi_lsbruto;
	      	$rk->lokasi_lsnetto = $request->lokasi_lsnetto;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$rk->unit_id  		= $request->unit_id;
	      	$rk->operator_id 	= $request->operator_id;
	      	$rk->driver_id 		= $request->driver_id;
	      	$rk->kasie_id  		= $user->id;
	      	$rk->status_id 		= $request->status_id;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->event 			= 'Update';
	      	$rkl->catatan 			= $request->catatan;
	      	$rkl->status_id_lama 	= 0;
	      	$rkl->save();

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
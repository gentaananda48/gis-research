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
use App\Model\LaporanMasalah;
use App\Model\Unit;
use App\Model\Lokasi;


class LaporanMasalahController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

	public function list(Request $request){
        $list = LaporanMasalah::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }
	public function show(Request $request) {
        $data = LaporanMasalah::find($request->id);
        return response()->json([
			'status'    => true, 
            'message'   => 'success', 
			'data' => $data
		]);

    }
    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = LaporanMasalah::where('updated_at', '>', $updated_at)->get();
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
	    	$laporan_masalah = new LaporanMasalah();
	    	$laporan_masalah->tanggal  = isset($post["tanggal"]) ? $post["tanggal"] : null;
			$laporan_masalah->unit_id  = isset($post["unit_id"]) ? $post["unit_id"] : null;
			$unit = Unit::find($request->unit_id);
			$laporan_masalah->unit_label = $unit->label;
			$laporan_masalah->lokasi_id  = isset($post["lokasi_id"]) ? $post["lokasi_id"] : null;
			$lokasi = Lokasi::find($request->lokasi_id);
			$laporan_masalah->lokasi_kode = $lokasi->kode;
			$laporan_masalah->lokasi_nama = $lokasi->nama;
			$laporan_masalah->laporan  = isset($post["laporan"]) ? $post["laporan"] : null;
			$laporan_masalah->kasie_id  = isset($post["kasie_id"]) ? $post["kasie_id"] : null;
			$kasie = User::find($request->kasie_id);
			$laporan_masalah->kasie_nama = $kasie->name;
			$laporan_masalah->driver_id = $user->id;
			$laporan_masalah->driver_nama = $user->name;
			$laporan_masalah->save();
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

	public function update(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
			$post = $request->all();
	    	$laporan_masalah = LaporanMasalah::find($request->id);
	    	$laporan_masalah->tanggal  = isset($post["tanggal"]) ? $post["tanggal"] : null;
			$laporan_masalah->unit_id  = isset($post["unit_id"]) ? $post["unit_id"] : null;
			$unit = Unit::find($request->unit_id);
			$laporan_masalah->unit_label = $unit->label;
			$laporan_masalah->lokasi_id  = isset($post["lokasi_id"]) ? $post["lokasi_id"] : null;
			$lokasi = Lokasi::find($request->lokasi_id);
			$laporan_masalah->lokasi_kode = $lokasi->kode;
			$laporan_masalah->lokasi_nama = $lokasi->nama;
			$laporan_masalah->laporan  = isset($post["laporan"]) ? $post["laporan"] : null;
			$laporan_masalah->kasie_id  = isset($post["kasie_id"]) ? $post["kasie_id"] : null;
			$kasie = User::find($request->kasie_id);
			$laporan_masalah->kasie_nama = $kasie->name;
			$laporan_masalah->driver_id = $user->id;
			$laporan_masalah->driver_nama = $user->name;
			$laporan_masalah->save();
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
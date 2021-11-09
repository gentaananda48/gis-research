<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Lokasi;
use App\Model\Unit;
use App\Model\TindakLanjutPending;
use App\Model\AlasanPending;
use App\Model\ReportParameterStandard;

class MasterDataController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function lokasi_list(Request $request){
        $list = Lokasi::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function lokasi_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Lokasi::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function unit_list(Request $request){
        $list = Unit::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function unit_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Unit::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function standard_list(Request $request){
        $list = ReportParameterStandard::leftJoin('aktivitas AS a', 'a.id', '=', 'report_parameter_standard.aktivitas_id')
            ->leftJoin('nozzle AS n', 'n.id', '=', 'report_parameter_standard.nozzle_id')
            ->leftJoin('volume_air AS v', 'v.id', '=', 'report_parameter_standard.volume_id')
            ->get(['report_parameter_standard.*', 'a.nama AS aktivitas_nama', 'n.nama AS nozzle_nama', 'v.volume']);
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function standard_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = ReportParameterStandard::leftJoin('aktivitas AS a', 'a.id', '=', 'report_parameter_standard.aktivitas_id')
            ->leftJoin('nozzle AS n', 'n.id', '=', 'report_parameter_standard.nozzle_id')
            ->leftJoin('volume_air AS v', 'v.id', '=', 'report_parameter_standard.volume_id')
            ->where('report_parameter_standard.updated_at', '>', $updated_at)
            ->get(['report_parameter_standard.*', 'a.nama AS aktivitas_nama', 'n.nama AS nozzle_nama', 'v.volume']);
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function alasan_pending_list(Request $request){
        $list = AlasanPending::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function alasan_pending_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
      $list = AlasanPending::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function tindak_lanjut_pending_list(Request $request){
        $list = TindakLanjutPending::get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function tindak_lanjut_pending_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = TindakLanjutPending::where('updated_at', '>', $updated_at)->get();
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
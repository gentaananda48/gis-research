<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Controllers\Controller;
use App\Model\Shift;
use App\Model\Lokasi;
use App\Model\Unit;
use App\Model\VAktivitas;
use App\Model\TindakLanjutPending;
use App\Model\AlasanPending;
use App\Model\VReportParameterStandard;
use App\Model\VUser;
use App\Model\Bahan;
use App\Model\SystemConfiguration;
use App\Model\ReportParameter;
use App\Model\ReportParameterBobot;
use App\Model\ReportParameterDefault;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterStandardDetail;

class MasterDataController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['lokasi_download_map']]);
    }

    public function shift_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = Shift::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function lokasi_sync_down(Request $request){
        $user = $this->guard()->user();
        $list_pg = !empty($user->area) ? explode(',',$user->area) : [];
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Lokasi::where('grup', $list_pg)->where('updated_at', '>', $updated_at)->get();
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

    public function aktivitas_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = VAktivitas::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function standard_sync_down(Request $request){
        $user = $this->guard()->user();
        $list_pg = !empty($user->area) ? explode(',',$user->area) : [];
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $query = VReportParameterStandard::where('updated_at', '>', $updated_at);
        if(!empty($list_pg)){
            $whereRaw = "(";
            foreach($list_pg as $k=>$pg){
                if($k>0){
                    $whereRaw .= "pg LIKE '%".$pg."%'";
                }
                $whereRaw .= "OR pg LIKE '%".$pg."%'";
            }
            $whereRaw = ")";
            $query->whereRaw($whereRaw);
        }
        $list = $query->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function user_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = VUser::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function bahan_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = Bahan::where('updated_at', '>', $updated_at)->get();
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

    public function tindak_lanjut_pending_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = TindakLanjutPending::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function report_parameter_standard_detail_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = ReportParameterStandardDetail::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function report_parameter_standard_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = ReportParameterStandard::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function report_parameter_bobot_sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list = ReportParameterBobot::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function lokasi_download_map(Request $request){
        $image_directory = SystemConfiguration::where('code', 'MAP_NDVI_IMAGE_PATH')->first(['value'])->value;
        $image_name = $request->kode.".png";
        try {
            $file = $image_directory.$request->kode.".png";
            $headers = array(
              'Content-Type: '. mime_content_type($file),
            );
            return FacadeResponse::download($file, $image_name, $headers);
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function guard(){
        return Auth::guard('api');
    }
}
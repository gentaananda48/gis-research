<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;

class ReportSegmentController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function all(Request $request){
        $data = array();

        try {
            $data['tgl'] = date('Y-m-d H:s');
            $data['pg_nama'] = "PG1";
            $data['lokasi_kode'] = "572B";
            $data['unit_label'] = "BSC - 03";
            $data['luasan_m2'] = rand(0, 10) / 10;
            $data['total_luasan_m2'] = rand(0, 10) / 10;
            $data['waktu_spray'] = date('Y-m-d H:s');
            $data['speed_standard'] = rand(0, 10) / 10;
            $data['speed_dibawah_standard'] = rand(0, 10) / 10;
            $data['speed_diatas_standard'] = rand(0, 10) / 10;
            $data['avg_speed'] = rand(0, 10) / 10;
            $data['arm_height_left_standard'] = rand(0, 10) / 10;
            $data['arm_height_left_dibawah_standard'] = rand(0, 10) / 10;
            $data['arm_height_left_diatas_standard'] = rand(0, 10) / 10;
            $data['avg_height_left'] = rand(0, 10) / 10;
            $data['arm_height_right_standard'] = rand(0, 10) / 10;
            $data['arm_height_right_dibawah_standard'] = rand(0, 10) / 10;
            $data['arm_height_right_diatas_standard'] = rand(0, 10) / 10;
            $data['avg_arm_height_right'] = rand(0, 10) / 10;
            $data['temperature_standard'] = rand(0, 10) / 10;
            $data['temperature_not_standard'] = rand(0, 10) / 10;
            $data['gloden_time_good'] = rand(0, 10) / 10;
            $data['gloden_time_poor'] = rand(0, 10) / 10;
            $data['ritase'] = rand(0,1);

            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
                'data'      => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'    => false, 
                'message'   => 'gagal ambil data', 
                'data'      => array()
            ]);
        }

    }

    public function guard(){
        return Auth::guard('api');
    }
}
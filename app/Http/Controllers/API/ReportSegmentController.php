<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\SummarySegmentLuasan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;

class ReportSegmentController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function all(Request $request){
        $data = array();
        $segment_luasan = SummarySegmentLuasan::get();

        try {
            foreach ($segment_luasan as $key => $value) {
                $data[$key]['tanggal_aktifitas'] = $value['tanggal_aktifitas'];
                $data[$key]['pg'] = $value['pg'];
                $data[$key]['lokasi'] = $value['lokasi'];
                $data[$key]['wilayah'] = $value['wilayah'];
                $data[$key]['unit'] = $value['unit'];
                $data[$key]['aktifitas'] = $value['aktifitas'];
                $data[$key]['shift'] = $value['shift'];
                $data[$key]['speed_standard'] = $value['speed_standard'];
                $data[$key]['speed_dibawah_standard'] = $value['speed_dibawah_standard'];
                $data[$key]['speed_diatas_standard'] = $value['speed_diatas_standard'];
                $data[$key]['speed_rata_rata'] = $value['speed_rata_rata'];
                $data[$key]['batas_atas_speed'] = $value['batas_atas_speed'];
                $data[$key]['batas_bawah_speed'] = $value['batas_bawah_speed'];
                $data[$key]['prc_ancakan'] = $value['prc_ancakan'];
                $data[$key]['prc_conf_wing_level_kiri_bawah_std'] = $value['prc_conf_wing_level_kiri_bawah_std'];
                $data[$key]['prc_conf_wing_level_kiri_dalam_std'] = $value['prc_conf_wing_level_kiri_dalam_std'];
                $data[$key]['prc_conf_wing_level_kiri_atas_std'] = $value['prc_conf_wing_level_kiri_atas_std'];
                $data[$key]['wing_level_kiri_rata_rata'] = $value['wing_level_kiri_rata_rata'];
                $data[$key]['batas_bawah_wing_level_kiri'] = $value['batas_bawah_wing_level_kiri'];
                $data[$key]['batas_atas_wing_level_kiri'] = $value['batas_atas_wing_level_kiri'];
                $data[$key]['prc_conf_wing_level_kanan_bawah_std'] = $value['prc_conf_wing_level_kanan_bawah_std'];
                $data[$key]['prc_conf_wing_level_kanan_dalam_std'] = $value['prc_conf_wing_level_kanan_dalam_std'];
                $data[$key]['prc_conf_wing_level_kanan_atas_std'] = $value['prc_conf_wing_level_kanan_atas_std'];
                $data[$key]['wing_level_kanan_rata_rata'] = $value['wing_level_kanan_rata_rata'];
                $data[$key]['batas_bawah_wing_level_kanan'] = $value['batas_bawah_wing_level_kanan'];
                $data[$key]['batas_atas_wing_level_kanan'] = $value['batas_atas_wing_level_kanan'];
                $data[$key]['batas_atas_wing_level_kanan'] = $value['batas_atas_wing_level_kanan'];
                $data[$key]['start_activity'] = $value['start_activity'];
                $data[$key]['end_activity'] = $value['end_activity'];
                $data[$key]['waktu_spray_detik'] = $value['waktu_spray_detik'];
                $data[$key]['prc_suhu_dalam_std'] = $value['prc_suhu_dalam_std'];
                $data[$key]['prc_suhu_tidak_std'] = $value['prc_suhu_tidak_std'];
                $data[$key]['suhu_rata_rata'] = $value['suhu_rata_rata'];
                $data[$key]['batas_suhu_std'] = $value['batas_suhu_std'];
                $data[$key]['gloden_time_good'] = $value['gloden_time_good'];
                $data[$key]['gloden_time_poor'] = $value['gloden_time_poor'];
            }

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
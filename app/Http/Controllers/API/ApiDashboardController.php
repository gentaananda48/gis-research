<?php

namespace App\Http\Controllers\API;

use App\Model\ApiDashboard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\RencanaKerja;
use App\Model\ReportConformity;
use App\Model\ReportParameterStandard;
use DateTime;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;


class ApiDashboardController extends Controller
{
    public function index()
    {
        $data = ApiDashboard::all();
        return response()->json([
            'status'    => true, 
            'message' => 'success',
            'data' => $data
        ]);
    }

    public function getDataByDate(Request $request){
        $data = array();
        $result = array();

        try {
            // Validate the input, if necessary
            // $this->validate($request, [
            //     'start_date' => 'required|date',
            //     'end_date' => 'required|date|after_or_equal:start_date',
            // ]);
            
            // Retrieve data from the database based on the date range
            $data = ReportConformity::when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                return $query->whereBetween(\DB::raw('DATE(tanggal)'), [$request->start_date, $request->end_date]);
            })->when($request->has('limit'), function ($query) use ($request) {
                return $query->limit($request->limit);
            })
            ->get();
            if ($data) {
                foreach ($data as $key => $value) {
                    $result[$key]['id'] = $value->id;
                    $result[$key]['tanggal_aktifitas'] = $value->tanggal;
                    $result[$key]['pg'] = $value->pg;
                    $result[$key]['lokasi'] = $value->lokasi;
                    $result[$key]['wilayah'] = $value->wilayah;
                    $result[$key]['unit'] = $value->unit;
                    $result[$key]['aktifitas'] = $value->activity;
                    $result[$key]['shift'] = $value->shift;
                    $result[$key]['prc_conf_speed_bawah_std'] = $value->speed_dibawah_standar;
                    $result[$key]['prc_conf_speed_dalam_std'] = $value->speed_standar;
                    $result[$key]['prc_conf_speed_atas_std'] = $value->speed_diatas_standar;
                    $result[$key]['speed_rata_rata'] = $value->avg_speed;
                    $result[$key]['batas_atas_speed'] = $value->batas_atas_speed;
                    $result[$key]['batas_bawah_speed'] = $value->batas_bawah_speed;
                    $result[$key]['prc_conf_ancakan'] = round($value->total_ancakan,2);
                    $result[$key]['prc_conf_wing_level_kiri_bawah_std'] = $value->wing_kiri_dibawah_standar;
                    $result[$key]['prc_conf_wing_level_kiri_dalam_std'] = $value->wing_kiri_standar;
                    $result[$key]['prc_conf_wing_level_kiri_atas_std'] = $value->wing_kiri_diatas_standar;
                    $result[$key]['wing_level_kiri_rata_rata'] = $value->avg_wing_kiri;
                    $result[$key]['batas_bawah_wing_level_kiri'] = $value->batas_bawah_wing_level_kiri;
                    $result[$key]['batas_atas_wing_level_kiri'] = $value->batas_atas_wing_level_kiri;
                    $result[$key]['prc_conf_wing_level_kanan_bawah_std'] = $value->wing_kanan_dibawah_standar;
                    $result[$key]['prc_conf_wing_level_kanan_dalam_std'] = $value->wing_kanan_standar;
                    $result[$key]['prc_conf_wing_level_kanan_atas_std'] = $value->wing_kanan_diatas_standar;
                    $result[$key]['wing_level_kanan_rata_rata'] = $value->avg_wing_kanan;
                    $result[$key]['batas_bawah_wing_level_kanan'] = $value->batas_bawah_wing_level_kanan;
                    $result[$key]['batas_atas_wing_level_kanan'] = $value->batas_atas_wing_level_kanan;
                    $result[$key]['start_activity'] = $value->start_activity;
                    $result[$key]['end_activity'] = $value->end_activity;
                    $result[$key]['waktu_spray_detik'] = $value->waktu_spray_detik;

                    $result[$key]['prc_suhu_dalam_std'] = $value->suhu_standar;
                    $result[$key]['prc_suhu_tidak_std'] = $value->suhu_tidak_standar;

                    $result[$key]['suhu_rata_rata'] = $value->suhu_avg;
                    $result[$key]['batas_suhu_std'] = $value->batas_suhu;
                    $result[$key]['gloden_time_good'] = $value->goldentime_tidak_standar;
                    $result[$key]['gloden_time_poor'] = $value->goldentime_standar;
                    $result[$key]['created_at'] = $value->created_at->format('Y-m-d H:i:s');
                    $result[$key]['updated_at'] = $value->updated_at->format('Y-m-d H:i:s');
                }
                
            }
            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
                'count'   => Count($result), 
                'data'      => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'    => false, 
                'line'      => $th->getLine(), 
                'message'   => $th->getMessage(), 
                'data'      => array()
            ]);
        }

    }
    

    public function dataByDateOld(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Validate the input, if necessary
        $this->validate($request, [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Retrieve data from the database based on the date range
        $data = ApiDashboard::whereBetween('tanggal_aktifitas', [$startDate, $endDate])->get();

        // Return the data as a JSON response
        return response()->json([
            'status'    => true, 
            'message' => 'success',
            'data' => $data
        ]);
    }

}



<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\ApiDashboard;
use App\Model\RencanaKerja;
use App\Model\ReportConformity;
use App\Model\ReportParameterStandard;
use DateTime;
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
            // Validate the input, if necessary
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            // Retrieve data from the database based on the date range
            $data = ApiDashboard::when($request->has('start_date') == true && $request->has('end_date') == true, function ($query) use ($request) {
                return $query->whereBetween(\DB::raw('DATE(tanggal_aktifitas)'), [$request->start_date, $request->end_date]);
            })->when($request->has('limit'), function ($query) use ($request) {
                return $query->limit($request->limit);
            })
            ->get();

            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
                'data'      => $data
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

    public function segment(Request $request){
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
                    $rk = RencanaKerja::where('unit_label', $value->unit)
                    ->where('tgl', $value->tanggal)
                    ->where('lokasi_kode', $value->lokasi)
                    ->first();
                    
                $report_param_standard = ReportParameterStandard::where('volume_id', $rk->volume_id)
                    ->where('nozzle_id', $rk->nozzle_id)
                    ->where('aktivitas_id', $rk->aktivitas_id)
                    ->with([
                        'reportParameterStandarDetails' => function($query) {
                            $query->where('point', 1);
                        },
                    ])
                    ->first();
                    
                    // total 
                    $total_ancakan = (($value->total_spraying/10000)/$rk->lokasi_lsnetto) * 100;

                    $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($value->unit)));
                    $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
                    $data_bsc = \DB::table($table_name)
                    ->select($table_name.'.*',$table_segment_label.".overlapping_route")
                    ->leftJoin($table_segment_label,$table_segment_label.'.lacak_bsc_id','=',$table_name.'.id')
                    ->where('lokasi_kode',$value->lokasi)
                    ->where($table_name.'.report_date',$value->tanggal)
                    ->where('speed','>',0.9);
                    
                    $data_bsc_avg = $data_bsc->avg('temperature_right');
                    $data_bsc_total = $data_bsc->get();

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
                    $result[$key]['batas_atas_speed'] = (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 1)->first()->range_1;
                    $result[$key]['batas_bawah_speed'] = (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 1)->first()->range_2;
                    $result[$key]['prc_conf_ancakan'] = round($total_ancakan,2);
                    $result[$key]['prc_conf_wing_level_kiri_bawah_std'] = $value->wing_kiri_dibawah_standar;
                    $result[$key]['prc_conf_wing_level_kiri_dalam_std'] = $value->wing_kiri_standar;
                    $result[$key]['prc_conf_wing_level_kiri_atas_std'] = $value->wing_kiri_diatas_standar;
                    $result[$key]['wing_level_kiri_rata_rata'] = $value->avg_wing_kiri;
                    $result[$key]['batas_bawah_wing_level_kiri'] = (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 4)->first()->range_1;
                    $result[$key]['batas_atas_wing_level_kiri'] = (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 4)->first()->range_2;
                    $result[$key]['prc_conf_wing_level_kanan_bawah_std'] = $value->wing_kanan_dibawah_standar;
                    $result[$key]['prc_conf_wing_level_kanan_dalam_std'] = $value->wing_kanan_standar;
                    $result[$key]['prc_conf_wing_level_kanan_atas_std'] = $value->wing_kanan_diatas_standar;
                    $result[$key]['wing_level_kanan_rata_rata'] = $value->avg_wing_kanan;
                    $result[$key]['batas_bawah_wing_level_kanan'] =(int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 5)->first()->range_1;
                    $result[$key]['batas_atas_wing_level_kanan'] =(int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 5)->first()->range_2;
                    $result[$key]['start_activity'] = date('H:i:s',$data_bsc_total->first()->utc_timestamp);
                    $result[$key]['end_activity'] = date('H:i:s',$data_bsc_total->last()->utc_timestamp);
                    $result[$key]['waktu_spray_detik'] = $data_bsc_total->last()->utc_timestamp - $data_bsc_total->first()->utc_timestamp;
                    $result[$key]['prc_suhu_dalam_std'] = $value->suhu_standar;
                    $result[$key]['prc_suhu_tidak_std'] = $value->suhu_tidak_standar;

                    if ($value->activity == 'Forcing' || $value->activity == 'Forcing 1' || $value->activity == 'Forcing 2' || $value->activity == 'Forcing 3') {
                        $batas_suhu = (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 6)->first()->range_2;
                        $suhu_avg = round($data_bsc_avg,2);
                    }else{
                        $batas_suhu = "";
                        $suhu_avg = "";
                    }

                    $result[$key]['suhu_rata_rata'] = $suhu_avg;
                    $result[$key]['batas_suhu_std'] = $batas_suhu;
                    $result[$key]['gloden_time_good'] = $value->goldentime_tidak_standar;
                    $result[$key]['gloden_time_poor'] = $value->goldentime_standar;
                }
                
            }
            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
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

    public function guard(){
        return Auth::guard('api');
    }
}
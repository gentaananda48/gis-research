<?php

namespace App\Http\Controllers\SummaryReportVAT;

use App\Center\GridCenter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\KoordinatLokasi;
use App\Model\Lacak;
use App\Model\Lacak2;
use App\Model\LacakBsc01;
use App\Model\PG;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportConformity;
use App\Model\ReportParameterStandard;
use App\Model\SystemConfiguration;
use App\Model\Unit;
use App\Model\VReportRencanaKerja2;
use App\Transformer\LacakBsc01Transformer;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportSummary;
use App\Exports\ReportConformityShow;
use App\Exports\ReportConformityDetail;
use App\Helper\GeofenceHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ConformityUnitController extends Controller
{
    
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($request->has('pg')) {
            $pg = $request->pg;
            Cache::put('pg',$request->pg,15);
        }else{
            if (Cache::has('pg')) {
                $pg = Cache::get('pg');
            }else{
                $pg[0] = 'All';
            }
        }

        if ($request->has('unit')) {
            $unit = $request->unit;
            Cache::put('unit',$request->unit,15);
        }else{
            if (Cache::has('unit')) {
                $unit = Cache::get('unit');
            }else{
                $unit[0] = 'All';
            }
        }

        if ($request->has('date_range')) {
            $date_range_request = $request->date_range;
            Cache::put('date_range',$request->date_range,15);
        }else{
            if (Cache::has('date_range')) {
                $date_range_request = Cache::get('date_range');
            }
        }

        // $user = $this->guard()->user();
        // $list_pg = explode(',', $user->area);
        if(!empty($date_range_request)){
            $date_range = explode(' - ', $date_range_request);
            $date1 = date('Y-m-d', strtotime($date_range[0]));
            $date2 = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $date1 = Carbon::now()->subDays(30)->format('Y-m-d');
            $date2 = date('Y-m-d');
        }

        $date_range = date('m/d/Y', strtotime($date1)).' - '.date('m/d/Y', strtotime($date2));
        $list_pg = array_merge(['All' => 'All'], PG::whereIn('nama', explode(',', $user->area))->pluck('nama', 'nama')->toArray());
        $list_unit = array_merge(['All' => 'All'], Unit::whereIn('pg', explode(',', $user->area))->pluck('label', 'label')->toArray());

        $report_conformities = new ReportConformity();

        $report_conformities = $report_conformities->whereBetween('tanggal', [$date1, $date2]);

        if($request->unit && !in_array("All", $request->unit)) {
            $report_conformities = $report_conformities->whereIn('unit', $request->unit);
        }

        if($request->pg && $request->pg[0] != 'All') {
            $report_conformities = $report_conformities->where('pg', $request->pg[0]);
        }

        $report_conformities = $report_conformities->groupBy('pg', 'unit')
        ->select([
            DB::raw("(SUM(speed_diatas_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_diatas_standar"),
            DB::raw("(SUM(speed_dibawah_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_dibawah_standar"),
            DB::raw("(SUM(speed_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_standar"),
            DB::raw("(SUM(wing_kiri_diatas_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_diatas_standar"),
            DB::raw("(SUM(wing_kiri_dibawah_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_dibawah_standar"),
            DB::raw("(SUM(wing_kiri_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_standar"),
            DB::raw("(SUM(wing_kanan_diatas_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_diatas_standar"),
            DB::raw("(SUM(wing_kanan_dibawah_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_dibawah_standar"),
            DB::raw("(SUM(wing_kanan_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_standar"),
            DB::raw("(SUM(goldentime_tidak_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_tidak_standar"),
            DB::raw("(SUM(goldentime_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_standar"),
            DB::raw("AVG(avg_wing_kiri) as wing_kiri_rusak"),
            DB::raw("AVG(avg_wing_kanan) as wing_kanan_rusak"),
            'pg', 'unit', 'tanggal', 'id'
        ])
        ->whereIn('pg', explode(',', $user->area))
        ->paginate(10);
        return view('summary_report_vat.conformity_unit.index', [
            'date_range'    => $date_range,
            'list_pg'       => $list_pg,
            'pg'            => $pg,
            'list_unit'     => $list_unit,
            'unit'          => $unit,
            'report_conformities' => $report_conformities,
            'hide_filter' => false
        ]); 
    }

    public function show(Request $request)
    {
        $date_ranges = explode(' - ', $request->range_date);
        $date1 = date('Y-m-d', strtotime($date_ranges[0]));
        $date2 = date('Y-m-d', strtotime($date_ranges[1]));
        $report_conformities = ReportConformity::where('pg', $request->pg)
            ->where('unit', $request->unit)
            ->whereBetween('tanggal', [$date1, $date2])
            ->orderBy('tanggal','asc')
            ->get();
        
        // if ($request->has('date') && $request->date != '') {
        //     $date1 = $request->date;
        //     $date2 = $request->date;    
        // }

        $report_conformity = ReportConformity::where('pg', $request->pg)
        ->where('unit', $request->unit)
        ->whereBetween('tanggal', [$date1, $date2])
        ->groupBy('pg', 'unit')
        ->select([
            DB::raw("(SUM(speed_diatas_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_diatas_standar"),
            DB::raw("(SUM(speed_dibawah_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_dibawah_standar"),
            DB::raw("(SUM(speed_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_standar"),
            DB::raw("(SUM(wing_kiri_diatas_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_diatas_standar"),
            DB::raw("(SUM(wing_kiri_dibawah_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_dibawah_standar"),
            DB::raw("(SUM(wing_kiri_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_standar"),
            DB::raw("(SUM(wing_kanan_diatas_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_diatas_standar"),
            DB::raw("(SUM(wing_kanan_dibawah_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_dibawah_standar"),
            DB::raw("(SUM(wing_kanan_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_standar"),
            DB::raw("(SUM(goldentime_tidak_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_tidak_standar"),
            DB::raw("(SUM(goldentime_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_standar"),
            DB::raw("AVG(avg_wing_kiri) as wing_kiri_rusak"),
            DB::raw("AVG(avg_wing_kanan) as wing_kanan_rusak"),
            'pg', 'unit', 'tanggal', 'id'
        ])->first();

        $date_range = array_unique($report_conformities->pluck('tanggal')->toArray());
       if($request->date) $report_conformities = $report_conformities->where('tanggal', $request->date);

        // $rencana_kerja = RencanaKerja::where('tgl', $request->date)
        //     ->whereIn('lokasi_kode', array_column($report_conformities->toArray(), 'lokasi'))
        //     ->get();
        
        $lokasi = array();
        if ($report_conformities) {
            foreach ($report_conformities as $value) {
                $lokasiTemp = KoordinatLokasi::where('lokasi',$value->lokasi)->get();
                if ($lokasiTemp) {
                    foreach ($lokasiTemp as $key => $valueChild) {
                        $lokasi[$value->lokasi][$key]['lat'] = $valueChild->latd;
                        $lokasi[$value->lokasi][$key]['lng'] = $valueChild->long;
                    }
                }
            }
        }
        
        $data_date = '';
        if($request->date){
            $data_date = $request->date;
        }
        return view('summary_report_vat.conformity_unit.show_1', [
            'date_range'    => $date_range,
            'range_date'    => $request->range_date,
            'report_conformity' => $report_conformity,
            'report_conformities' => $report_conformities,
            // 'rencana_kerja' => $rencana_kerja,
            'pg' => $request->pg,
            'unit' => $request->unit,
            'lokasi' => $lokasi,
            'data_date' => $data_date
        ]);
    }

    public function detail(Request $request,$id)
    {
        $report_conformity = ReportConformity::find($id);

        $rk = RencanaKerja::where('unit_label', $report_conformity->unit)
            ->where('tgl', $report_conformity->tanggal)
            ->where('lokasi_kode', $report_conformity->lokasi)
            ->first();

        $explodeRk = explode(" ",$rk->aktivitas_nama); 

        $report_param_standard = ReportParameterStandard::where('volume_id', $rk->volume_id)
            ->where('nozzle_id', $rk->nozzle_id)
            ->where('aktivitas_id', $rk->aktivitas_id)
            ->with([
                'reportParameterStandarDetails' => function($query) {
                    $query->where('point', 1);
                },
            ])
            ->first();

        $list_rrk = array();
        // $cache_key = env('APP_CODE').':LOKASI:LIST_ReportConformity_Ritase_'.$rk->id;
        // $cached = Redis::get($cache_key);
        // if(isset($cached)) {
        //     $list_rrk = $cached;
        // } else {
        //     $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get();

        //     Redis::set($cache_key, $list_rrk);
        // }
        
        // $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->get();
        $header = [];

        // foreach ($list_rks as $rks) {
        //     if ($rks->ritase == 999) {
        //         $header[$rks->parameter_id] = $rks->parameter_nama;
        //     }
        // }

        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;

        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT_'.$rk->lokasi_kode;
        $cached = Redis::get($cache_key);
        $list_koordinat_lokasi = [];
        if(isset($cached)) {
            $list_koordinat_lokasi = json_decode($cached, FALSE);
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::where('lokasi', $rk->lokasi_kode)
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            Redis::set($cache_key, json_encode($list_koordinat_lokasi));
        }

        $list_lokasi = [];
        foreach($list_koordinat_lokasi as $v){
            if($v->lokasi==$rk->lokasi_kode){
                $idx = $v->lokasi.'_'.$v->bagian;
                if(array_key_exists($idx, $list_lokasi)){
                    $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
                } else {
                    $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
                }
            }
        }
        $list_lokasi = array_values($list_lokasi);

        // $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
        // $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
        // $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$rk->unit_source_device_id;
        // if(in_array($rk->unit_source_device_id, $offline_units)){
        //     $cache_key = env('APP_CODE').':UNIT:PLAYBACK2_'.$rk->unit_source_device_id;
        // }
        // $tgl = $rk->tgl;
        // if($tgl >= date('Y-m-d')) {
        //     $redis_scan_result = Redis::scan(0, 'match', $cache_key.'_'.$tgl.'*');
        //     $cache_key = $cache_key.'_'.$jam_selesai;
        //     if(count($redis_scan_result[1])>0){
        //         rsort($redis_scan_result[1]);
        //         $last_key = $redis_scan_result[1][0];
        //         if($cache_key<$last_key){
        //             $cache_key = $last_key;
        //         }
        //         foreach($redis_scan_result[1] as $key){
        //             if($key!=$cache_key){
        //                 Redis::del($key);
        //             }
        //         }
        //     }
        // } else {
        //     $cache_key = $cache_key.'_'.$tgl;
        // }
        // $cached = Redis::get($cache_key);
        // $list_lacak = [];
        // if(isset($cached)) {
        //     $list_lacak = json_decode($cached, FALSE);
        // } else {
        //     $timestamp_1 = strtotime($rk->tgl.' 00:00:00');
        //     $timestamp_2 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($rk->tgl.' 23:59:59');
        //     //
        //     if(in_array($rk->unit_source_device_id, $offline_units)){
        //         $table_name = 'lacak_'.$rk->unit_source_device_id;
        //         $list_lacak = DB::table($table_name)
        //             ->where('report_date', $tgl)
        //             //->where('utc_timestamp', '>=', $timestamp_1)
        //             //->where('utc_timestamp', '<=', $timestamp_2)
        //             ->orderBy('utc_timestamp', 'ASC')
        //             ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, pump_switch_right, pump_switch_left, pump_switch_main, arm_height_right, arm_height_left, `utc_timestamp` AS timestamp")
        //             ->get();
        //     } else {
        //         if($rk->tgl>='2022-03-15') {
        //             $list_lacak = Lacak2::where('ident', $rk->unit_source_device_id)
        //                 ->where('timestamp', '>=', $timestamp_1)
        //                 ->where('timestamp', '<=', $timestamp_2)
        //                 ->orderBy('timestamp', 'ASC')
        //                 ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
        //         } else {
        //             $list_lacak = Lacak::where('ident', $rk->unit_source_device_id)
        //                 ->where('timestamp', '>=', $timestamp_1)
        //                 ->where('timestamp', '<=', $timestamp_2)
        //                 ->orderBy('timestamp', 'ASC')
        //                 ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
        //         }
        //     }
        //     //
        //     Redis::set($cache_key, json_encode($list_lacak), 'EX', 2592000);
        // }

         // ADJUSTMENT CODE SUMMARY FROM REDIS
        $cacheKey = env('APP_CODE') . ':RK_SUMMARY_' . $rk->id;
        $summary = Redis::get($cacheKey);

        if ($summary === null) {
        // Data not found in Redis, retrieve from the database
        $list_rrk_array = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get()->toArray();
        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->get();
        $header = [];
        $rata2 = [];
        $poin = [];
        $kualitas = '-';

        foreach ($list_rks as $rks) {
            if ($rks->ritase == 999) {
                $header[$rks->parameter_id] = $rks->parameter_nama;
                $rata2[$rks->parameter_id] = $rks->parameter_id != 2 ? number_format($rks->realisasi, 2) : $rks->realisasi;
                $poin[$rks->parameter_id] = $rks->nilai_bobot;
            } else if ($rks->ritase == 999999) {
                $poin[999] = $rks->nilai_bobot;
                $kualitas = $rks->kualitas;
            }
        }

        $summary = (object) [
            'header' => $header,
            'ritase' => $list_rrk_array,
            'rata2' => $rata2,
            'poin' => $poin,
            'kualitas' => $kualitas,
        ];

        // Store the retrieved data in Redis
        Redis::set($cacheKey, json_encode($summary), 'EX', 2592000);
        } else {
            // Data found in Redis, retrieve it
            $decodedSummary = json_decode($summary, true);

            if ($decodedSummary !== null) {
                // Decoding was successful
                $summary = (object) $decodedSummary;
                // Access the properties
                $header = $summary->header;
            }
        }

        $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($report_conformity->unit)));
        $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
        $data_bsc = DB::table($table_name)
        ->select($table_name.'.*',$table_segment_label.".overlapping_route")
        ->leftJoin($table_segment_label,$table_segment_label.'.lacak_bsc_id','=',$table_name.'.id')
        ->where('lokasi_kode',$report_conformity->lokasi)
        ->where($table_name.'.report_date',$report_conformity->tanggal)
        ->where('speed','>',0.9)
        ->get();
        $lacak_bsc = array();
        if ($data_bsc) {
            $loop = 0;
            $luasan = 0;
            foreach ($data_bsc as $key => $value) {
                // $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
                // $data_segment = DB::table($table_segment_label)
                // ->leftJoin($table_name,$table_name.'.id','=',$table_segment_label.'.lacak_bsc_id')
                // ->where($table_segment_label.'.lacak_bsc_id',$value->id)
                // ->where('overlapping_route',1)
                // ->count();

                $lacak_bsc[$key]['is_overlapping'] = $value->overlapping_route == "1" ? 1:0;
                $lacak_bsc[$key]['position_latitude'] = $value->latitude;
                $lacak_bsc[$key]['position_longitude'] = $value->longitude;
                $lacak_bsc[$key]['position_altitude'] = $value->altitude;
                $lacak_bsc[$key]['position_direction'] = $value->bearing;
                $lacak_bsc[$key]['position_speed'] = $value->speed;
                $lacak_bsc[$key]['pump_switch_right'] = $value->pump_switch_right;
                $lacak_bsc[$key]['pump_switch_left'] = $value->pump_switch_left;
                $lacak_bsc[$key]['pump_switch_main'] = $value->pump_switch_main;
                $lacak_bsc[$key]['arm_height_right'] = $value->arm_height_right;
                $lacak_bsc[$key]['arm_height_left'] = $value->arm_height_left;
                $lacak_bsc[$key]['timestamp'] = $value->utc_timestamp;
                
                // $startID = $value->id - 11;
                // $overlapping = 0;
                // $geofenceHelper = new GeofenceHelper;
                // $getDataBsc = DB::table($table_name)
                // ->select('latitude','longitude')
                // ->where('lokasi_kode',$report_conformity->lokasi)
                // ->where('report_date',$report_conformity->tanggal)
                // ->where('speed','>',0.9)
                // ->whereBetween('id',[$data_bsc->first()->id,$startID])
                // ->get()
                // ->toArray();

                // if ($getDataBsc) {
                //     $overlapping = $geofenceHelper->calculateOverlap($value->latitude, $value->longitude, $getDataBsc);
                // }

                // \Log::info($overlapping);
                // // end cek kondisi
                // if ($overlapping != 1) {
                //     continue;
                // }

                // $lacak_overlapping[$loop]['position_latitude'] = $value->latitude;
                // $lacak_overlapping[$loop]['position_longitude'] = $value->longitude;
                // $lacak_overlapping[$loop]['position_altitude'] = $value->altitude;
                // $lacak_overlapping[$loop]['position_direction'] = $value->bearing;
                // $lacak_overlapping[$loop]['position_speed'] = $value->speed;
                // $lacak_overlapping[$loop]['pump_switch_right'] = $value->pump_switch_right;
                // $lacak_overlapping[$loop]['pump_switch_left'] = $value->pump_switch_left;
                // $lacak_overlapping[$loop]['pump_switch_main'] = $value->pump_switch_main;
                // $lacak_overlapping[$loop]['arm_height_right'] = $value->arm_height_right;
                // $lacak_overlapping[$loop]['arm_height_left'] = $value->arm_height_left;
                // $lacak_overlapping[$loop]['timestamp'] = $value->utc_timestamp;
                // $loop++;
            }
        }
        

        // $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
        // $data_segment = DB::table($table_segment_label)
        // ->leftJoin($table_name,$table_name.'.id','=',$table_segment_label.'.lacak_bsc_id')
        // ->where('kode_lokasi',$report_conformity->lokasi)
        // ->where($table_segment_label.'.report_date',$report_conformity->tanggal)
        // ->where('overlapping_route',1)
        // ->get();

        // $lacak_overlapping = array();
        // if ($data_segment) {
        //     foreach ($data_segment as $key => $value) {
        //         $lacak_overlapping[$key]['position_latitude'] = $value->latitude;
        //         $lacak_overlapping[$key]['position_longitude'] = $value->longitude;
        //         $lacak_overlapping[$key]['position_altitude'] = $value->altitude;
        //         $lacak_overlapping[$key]['position_direction'] = $value->bearing;
        //         $lacak_overlapping[$key]['position_speed'] = $value->speed;
        //         $lacak_overlapping[$key]['pump_switch_right'] = $value->pump_switch_right;
        //         $lacak_overlapping[$key]['pump_switch_left'] = $value->pump_switch_left;
        //         $lacak_overlapping[$key]['pump_switch_main'] = $value->pump_switch_main;
        //         $lacak_overlapping[$key]['arm_height_right'] = $value->arm_height_right;
        //         $lacak_overlapping[$key]['arm_height_left'] = $value->arm_height_left;
        //         $lacak_overlapping[$key]['timestamp'] = $value->utc_timestamp;
        //     }
        // }
        
        $lacak_overlapping = array();
        $list_rrk = '{}';
        $avgRRK = collect(json_decode($list_rrk))->avg('parameter_6');
        return view('summary_report_vat.conformity_unit.show_2', [
            'report_conformity' => $report_conformity,
            'rk' => $rk,
            'list_rrk' => json_decode($list_rrk),
            'header' => $header,
            'summary'       => $summary,
            'timestamp_jam_mulai'   => strtotime($jam_mulai),
            'timestamp_jam_selesai' => strtotime($jam_selesai),
            'list_lacak'    => json_encode($lacak_bsc),
            'list_overlapping'    => json_encode($lacak_overlapping),
            'list_lokasi'   => json_encode($list_lokasi),
            'report_param_standard' => $report_param_standard,
            'pg' => $request->pg,
            'unit' => $request->unit,
            'avgRRK' => $avgRRK,
            'explodeRk' => $explodeRk[0]
        ]);
    }

    private function getDatesFromRange($date_time_from, $date_time_to)
    {

        // cut hours, because not getting last day when hours of time to is less than hours of time_from
        // see while loop
        $start = Carbon::createFromFormat('Y-m-d', substr($date_time_from, 0, 10));
        $end = Carbon::createFromFormat('Y-m-d', substr($date_time_to, 0, 10));

        $dates = [];

        while ($start->lte($end)) {

            $dates[] = $start->copy()->format('Y-m-d');

            $start->addDay();
        }

        return $dates;
    }

    function export(Request $request,$type){
        $user = Auth::user();
        if(!empty($request->range)){
            $date_range = explode(' - ', $request->range);
            $date1 = date('Y-m-d', strtotime($date_range[0]));
            $date2 = date('Y-m-d', strtotime($date_range[1]));
        } else {
            $date1 = date('Y-m-d');
            $date2 = date('Y-m-d');
        }

        $date_range = date('m/d/Y', strtotime($date1)).' - '.date('m/d/Y', strtotime($date2));
        $list_pg = array_merge(['All' => 'All'], PG::all(['nama'])->pluck('nama', 'nama')->toArray());
        $list_unit = array_merge(['All' => 'All'], Unit::all(['label'])->pluck('label', 'label')->toArray());

        $report_conformities = new ReportConformity();

        $report_conformities = $report_conformities->whereBetween('tanggal', [$date1, $date2]);

        if($request->unit && $request->unit != 'All') {
            $report_conformities = $report_conformities->where('unit', $request->unit);
        }

        if($request->pg && $request->pg != 'All') {
            $report_conformities = $report_conformities->where('pg', $request->pg);
        }

        $report_conformities = $report_conformities->groupBy('pg', 'unit')
        ->select([
            DB::raw("(SUM(speed_diatas_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_diatas_standar"),
            DB::raw("(SUM(speed_dibawah_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_dibawah_standar"),
            DB::raw("(SUM(speed_standar)/(SUM(speed_diatas_standar) + SUM(speed_dibawah_standar) + SUM(speed_standar)) * 100) as speed_standar"),
            DB::raw("(SUM(wing_kiri_diatas_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_diatas_standar"),
            DB::raw("(SUM(wing_kiri_dibawah_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_dibawah_standar"),
            DB::raw("(SUM(wing_kiri_standar)/(SUM(wing_kiri_diatas_standar) + SUM(wing_kiri_dibawah_standar) + SUM(wing_kiri_standar)) * 100) as wing_kiri_standar"),
            DB::raw("(SUM(wing_kanan_diatas_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_diatas_standar"),
            DB::raw("(SUM(wing_kanan_dibawah_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_dibawah_standar"),
            DB::raw("(SUM(wing_kanan_standar)/(SUM(wing_kanan_diatas_standar) + SUM(wing_kanan_dibawah_standar) + SUM(wing_kanan_standar)) * 100) as wing_kanan_standar"),
            DB::raw("(SUM(goldentime_tidak_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_tidak_standar"),
            DB::raw("(SUM(goldentime_standar)/(SUM(goldentime_tidak_standar) + SUM(goldentime_standar)) * 100) as goldentime_standar"),
            DB::raw("(SUM(suhu_tidak_standar)/(SUM(suhu_tidak_standar) + SUM(suhu_standar)) * 100) as suhu_tidak_standar"),
            DB::raw("(SUM(suhu_standar)/(SUM(suhu_tidak_standar) + SUM(suhu_standar)) * 100) as suhu_standar"),
            DB::raw("AVG(avg_wing_kiri) as wing_kiri_rusak"),
            DB::raw("AVG(avg_wing_kanan) as wing_kanan_rusak"),
            'pg', 'unit', 'tanggal', 'id'
        ])
        ->whereIn('pg', explode(',', $user->area))
        ->get();

        $result['summary'] = $report_conformities; 
        $result['date'] = $request->range; 
        return Excel::download(new ExportSummary($result), 'summary.xlsx');
    }

    function export_show(Request $request){
        $date_ranges = explode(' - ', $request->range);
        if ($request->has('date') && $request->date != '') {
            $date1 = $request->date;
            $date2 = $request->date;    
        }else{
            $date1 = date('Y-m-d', strtotime($date_ranges[0]));
            $date2 = date('Y-m-d', strtotime($date_ranges[1]));
        }
        
        $report_conformities = ReportConformity::where('pg', $request->pg)
            ->where('unit', $request->unit)
            ->whereBetween('tanggal', [$date1, $date2])
            ->get();

            return Excel::download(new ReportConformityShow($report_conformities), 'ReportConformityShow.xlsx');
    }

    function export_detail(Request $request,$id){
        $report_conformity = ReportConformity::find($id);
        $rk = RencanaKerja::where('unit_label', $report_conformity->unit)
            ->where('tgl', $report_conformity->tanggal)
            ->where('lokasi_kode', $report_conformity->lokasi)
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

        $explodeRk = explode(" ",$rk->aktivitas_nama); 
        $cache_key = env('APP_CODE').':LOKASI:LIST_ReportConformity_Ritase_'.$rk->id;
        $list_rrk = Redis::get($cache_key);
        $avgRRK = collect(json_decode($list_rrk))->avg('parameter_6');

        $result['report_conformity'] = $report_conformity; 
        $result['avgRRK'] = $avgRRK;
        $result['report_param_standard'] = $report_param_standard;
        $result['explodeRk'] = $explodeRk;

        return Excel::download(new ReportConformityDetail($result), 'ReportConformityDetail.xlsx');
    }

    function ritaseAjax(Request $request,$id){
        $result = array();

        $result['status'] = '200';
        $result['message'] = "data success";

        $report_conformity = ReportConformity::find($id);

        $rk = RencanaKerja::where('unit_label', $report_conformity->unit)
            ->where('tgl', $report_conformity->tanggal)
            ->where('lokasi_kode', $report_conformity->lokasi)
            ->first();

        $explodeRk = explode(" ",$rk->aktivitas_nama); 

        $report_param_standard = ReportParameterStandard::where('volume_id', $rk->volume_id)
            ->where('nozzle_id', $rk->nozzle_id)
            ->where('aktivitas_id', $rk->aktivitas_id)
            ->with([
                'reportParameterStandarDetails' => function($query) {
                    $query->where('point', 1);
                },
            ])
            ->first();

        $list_rrk = array();
        $cache_key = env('APP_CODE').':LOKASI:LIST_ReportConformity_Ritase_'.$rk->id;
        $cached = Redis::get($cache_key);
        if(isset($cached)) {
            $list_rrk = $cached;
        } else {
            $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get();

            Redis::set($cache_key, $list_rrk);
        }
        
        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->get();
        $header = [];

        foreach ($list_rks as $rks) {
            if ($rks->ritase == 999) {
                $header[$rks->parameter_id] = $rks->parameter_nama;
            }
        }

        $list_rrk = json_decode($list_rrk);
        $result['html'] = view('summary_report_vat.conformity_unit._partial_ritase',compact('list_rrk','header'))->render();
        return $result;
    }
}

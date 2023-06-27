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
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ConformityUnitController extends Controller
{
    
    public function index(Request $request)
    {
        // $user = $this->guard()->user();
        // $list_pg = explode(',', $user->area);
        if(!empty($request->date_range)){
            $date_range = explode(' - ', $request->date_range);
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

        if($request->unit && $request->unit[0] != 'All') {
            $report_conformities = $report_conformities->where('unit', $request->unit[0]);
        }

        if($request->pg && $request->pg[0] != 'All') {
            $report_conformities = $report_conformities->where('pg', $request->pg[0]);
        }

        $report_conformities = $report_conformities->groupBy('pg', 'unit', 'tanggal')
        ->select([
            DB::raw("SUM(speed_diatas_standar) as speed_diatas_standar"),
            DB::raw("SUM(speed_dibawah_standar) as speed_dibawah_standar"),
            DB::raw("SUM(speed_standar) as speed_standar"),
            DB::raw("SUM(wing_kiri_diatas_standar) as wing_kiri_diatas_standar"),
            DB::raw("SUM(wing_kiri_dibawah_standar) as wing_kiri_dibawah_standar"),
            DB::raw("SUM(wing_kiri_standar) as wing_kiri_standar"),
            DB::raw("SUM(wing_kanan_diatas_standar) as wing_kanan_diatas_standar"),
            DB::raw("SUM(wing_kanan_dibawah_standar) as wing_kanan_dibawah_standar"),
            DB::raw("SUM(wing_kanan_standar) as wing_kanan_standar"),
            DB::raw("SUM(goldentime_tidak_standar) as goldentime_tidak_standar"),
            DB::raw("SUM(goldentime_standar) as goldentime_standar"),
            'pg', 'unit', 'tanggal', 'id'
        ])
        ->paginate(10);

        return view('summary_report_vat.conformity_unit.index', [
            'date_range'    => $date_range,
            'list_pg'       => $list_pg,
            'pg'            => $request->pg,
            'list_unit'     => $list_unit,
            'unit'          => $request->unit,
            'report_conformities' => $report_conformities
        ]); 
    }

    public function show(Request $request, $id)
    {
        $report_conformity = ReportConformity::find($id);
        $report_conformities = ReportConformity::where('pg', $report_conformity->pg)
            ->where('unit', $report_conformity->unit)
            ->get();

        $date_range = array_unique($report_conformities->pluck('tanggal')->toArray());

        $report_conformities = $report_conformities->where('tanggal', $request->date);

        $rencana_kerja = RencanaKerja::where('tgl', $request->date)
            ->whereIn('lokasi_kode', array_column($report_conformities->toArray(), 'lokasi'))
            ->get();

        return view('summary_report_vat.conformity_unit.show_1', [
            'date_range'    => $date_range,
            'report_conformity' => $report_conformity,
            'report_conformities' => $report_conformities,
            'rencana_kerja' => $rencana_kerja
        ]);
    }

    public function detail(Request $request, $id)
    {

        $report_conformity = ReportConformity::find($id);
        $report_conformities = ReportConformity::where('pg', $report_conformity->pg)
            ->where('unit', $report_conformity->unit)
            ->get();

        $report_conformities = $report_conformities->where('tanggal', $request->date);

        $rk = RencanaKerja::where('unit_label', $report_conformity->unit)
            ->where('tgl', $report_conformity->tanggal)
            ->where('lokasi_kode', $report_conformity->lokasi)
            ->first();


        $report_param_standard = ReportParameterStandard::where('volume_id', $rk->volume_id)
            ->where('nozzle_id', $rk->nozzle_id)
            ->where('aktivitas_id', $rk->aktivitas_id)
            ->with([
                'reportParameterStandarDetails' => function($query) {
                    $query->where('urutan', 2);
                },
            ])
            ->first();

        $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get()->toArray();

        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->get();
        $header = [];

        foreach ($list_rks as $rks) {
            if ($rks->ritase == 999) {
                $header[$rks->parameter_id] = $rks->parameter_nama;
            }
        }

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

        $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
        $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$rk->unit_source_device_id;
        if(in_array($rk->unit_source_device_id, $offline_units)){
            $cache_key = env('APP_CODE').':UNIT:PLAYBACK2_'.$rk->unit_source_device_id;
        }
        $tgl = $rk->tgl;
        if($tgl >= date('Y-m-d')) {
            $redis_scan_result = Redis::scan(0, 'match', $cache_key.'_'.$tgl.'*');
            $cache_key = $cache_key.'_'.$jam_selesai;
            if(count($redis_scan_result[1])>0){
                rsort($redis_scan_result[1]);
                $last_key = $redis_scan_result[1][0];
                if($cache_key<$last_key){
                    $cache_key = $last_key;
                }
                foreach($redis_scan_result[1] as $key){
                    if($key!=$cache_key){
                        Redis::del($key);
                    }
                }
            }
        } else {
            $cache_key = $cache_key.'_'.$tgl;
        }
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = strtotime($rk->tgl.' 00:00:00');
            $timestamp_2 = $rk->tgl >= date('Y-m-d') ? strtotime($jam_selesai) : strtotime($rk->tgl.' 23:59:59');
            //
            if(in_array($rk->unit_source_device_id, $offline_units)){
                $table_name = 'lacak_'.$rk->unit_source_device_id;
                $list_lacak = DB::table($table_name)
                    ->where('report_date', $tgl)
                    //->where('utc_timestamp', '>=', $timestamp_1)
                    //->where('utc_timestamp', '<=', $timestamp_2)
                    ->orderBy('utc_timestamp', 'ASC')
                    ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, pump_switch_right, pump_switch_left, pump_switch_main, arm_height_right, arm_height_left, `utc_timestamp` AS timestamp")
                    ->get();
            } else {
                if($rk->tgl>='2022-03-15') {
                    $list_lacak = Lacak2::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                } else {
                    $list_lacak = Lacak::where('ident', $rk->unit_source_device_id)
                        ->where('timestamp', '>=', $timestamp_1)
                        ->where('timestamp', '<=', $timestamp_2)
                        ->orderBy('timestamp', 'ASC')
                        ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_1 AS pump_switch_right', 'din_2 AS pump_switch_left', 'din_3 AS pump_switch_main', 'payload_text', 'timestamp']);
                }
            }
            //
            Redis::set($cache_key, json_encode($list_lacak), 'EX', 2592000);
        }

         // ADJUSTMENT CODE SUMMARY FROM REDIS
        $cacheKey = env('APP_CODE') . ':RK_SUMMARY_' . $rk->id;
        $summary = Redis::get($cacheKey);

        if ($summary === null) {
        // Data not found in Redis, retrieve from the database
        $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $id)->get()->toArray();
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
            'ritase' => $list_rrk,
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

        return view('summary_report_vat.conformity_unit.show_2', [
            'report_conformity' => $report_conformity,
            'report_conformities' => $report_conformities,
            'rk' => $rk,
            'list_rrk' => $list_rrk,
            'header' => $header,
            'summary'       => $summary,
            'timestamp_jam_mulai'   => strtotime($jam_mulai),
            'timestamp_jam_selesai' => strtotime($jam_selesai),
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'report_param_standard' => $report_param_standard
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
}

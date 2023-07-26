<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Center\GridCenter;
use App\Model\Tracker;
use App\Transformer\UserTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helper\GeofenceHelper;
use App\Model\KoordinatLokasi;
use App\Model\KoordinatLokasiTemp;
use App\Model\Lacak;
use App\Model\Unit;
use App\Model\RencanaKerja;
use App\Model\ReportRencanaKerja;
use App\Model\ReportParameter;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportStatus;
use App\Model\Aktivitas;
use App\Model\VReportRencanaKerja;
use App\Model\VReportRencanaKerja2;
use App\Model\SystemConfiguration;
use App\Model\Log;
use Illuminate\Support\Facades\Cache;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['users','test', 'privacy']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function download(){ 
        // try {
        //     $myFile = storage_path("downloads\app-release.apk");
        //     return respond()->download($myFile);
        // } catch (\Throwable $th) {
        //     throw $th;
        // }   
            $path = public_path('downloads/app-release.apk');
            
            return response()->file($path ,[
            'Content-Type'=>'application/vnd.android.package-archive',
            'Content-Disposition'=> 'attachment; filename="android.apk"',
        ]);

    }
    
    public function index(Request $request){
        $user = $this->guard()->user();
        $list_pg = explode(',', $user->area);
        if(!empty($request->date_range)){
            $date_range = explode(' - ', $request->date_range);
            $date1 = date('Y-m-d', strtotime($date_range[0]));
            $date2 = date('Y-m-d', strtotime($date_range[1]));
        } else {
            //$date1 = date('Y-m-d', strtotime('-6 day'));
            $date1 = date('Y-m-01');
            $date2 = date('Y-m-d');
        }
        $query = RencanaKerja::groupBy('lokasi_grup')
            ->whereIn('lokasi_grup', $list_pg)
            ->whereBetween('tgl', [$date1, $date2]);
            if(!empty($request->pg)) {
                $query->whereIn('lokasi_grup', $request->pg);
            }
            if(!empty($request->aktivitas)) {
                $query->whereIn('aktivitas_kode', $request->aktivitas);
            }
            if(!empty($request->kualitas)) {
                $query->whereIn('kualitas', $request->kualitas);
            }
        $res = $query->orderBy('lokasi_grup', 'ASC')
            ->selectRaw('lokasi_grup, count(1) AS jumlah_rk, SUM(CASE WHEN status_id = 4 THEN 1 ELSE 0 END) AS jumlah_report')
            ->get();
        $list_chart_1a = ['label'=>[], 'data'=>[]];
        $list_chart_1b = ['label'=>[], 'data'=>[]];
        $total_rk = 0;
        $total_real = 0;
        foreach($res as $v){
            $list_chart_1a['label'][] = $v->lokasi_grup;
            $list_chart_1a['data'][] = $v->jumlah_rk;
            $total_rk += $v->jumlah_rk;

            $list_chart_1b['label'][] = $v->lokasi_grup;
            $list_chart_1b['data'][] = $v->jumlah_report;
            $total_real += $v->jumlah_report;
        }
        $perc_rk_real = $total_rk == 0 ? 0 : number_format($total_real / $total_rk * 100, 2);

        $query = RencanaKerja::leftJoin('report_status', 'report_status.status', '=', 'rencana_kerja.kualitas')
            ->whereIn('lokasi_grup', $list_pg)
            ->whereBetween('tgl', [$date1, $date2]);
            if(!empty($request->pg)) {
                $query->whereIn('lokasi_grup', $request->pg);
            }
            if(!empty($request->aktivitas)) {
                $query->whereIn('aktivitas_kode', $request->aktivitas);
            }
            if(!empty($request->kualitas)) {
                $query->whereIn('kualitas', $request->kualitas);
            }
        $res = $query->whereRaw("status_id = 4 and jam_laporan IS NOT NULL and kualitas IS NOT NULL and kualitas <> '-'")
            ->whereIn('lokasi_grup', $list_pg)
            ->groupBy('kualitas')
            ->orderBy('report_status.id', 'ASC')
            ->selectRaw('kualitas, count(1) as jumlah')
            ->get();
        $list_chart_2 = ['label'=>[], 'data'=>[]];
        foreach($res as $v){
            $list_chart_2['label'][] = $v->kualitas;
            $list_chart_2['data'][] = $v->jumlah;
        }

        $query = RencanaKerja::whereRaw("status_id = 4 and jam_laporan IS NOT NULL and kualitas IN ('Poor', 'Very Poor')")
            ->whereIn('lokasi_grup', $list_pg)
            ->whereBetween('tgl', [$date1, $date2]);
            if(!empty($request->pg)) {
                $query->whereIn('lokasi_grup', $request->pg);
            }
            if(!empty($request->aktivitas)) {
                $query->whereIn('aktivitas_kode', $request->aktivitas);
            }
            if(!empty($request->kualitas)) {
                $query->whereIn('kualitas', $request->kualitas);
            }
        $list_data_rk_poor = $query->orderBy('tgl', 'ASC')
            ->get(['id', 'tgl', 'lokasi_grup', 'lokasi_kode', 'aktivitas_nama', 'unit_label', 'kualitas']);

        $query = RencanaKerja::leftJoin('report_status', 'report_status.status', '=', 'rencana_kerja.kualitas')
            ->whereIn('lokasi_grup', $list_pg)
            ->whereBetween('tgl', [$date1, $date2]);
            if(!empty($request->pg)) {
                $query->whereIn('lokasi_grup', $request->pg);
            }
            if(!empty($request->aktivitas)) {
                $query->whereIn('aktivitas_kode', $request->aktivitas);
            }
            if(!empty($request->kualitas)) {
                $query->whereIn('kualitas', $request->kualitas);
            }
        $res = $query->whereRaw("status_id = 4 and jam_laporan IS NOT NULL")
            ->whereIn('lokasi_grup', $list_pg)
            ->groupBy('unit_label')
            ->groupBy('kualitas')
            ->orderBy('report_status.id', 'DESC')
            ->selectRaw('unit_label, kualitas, count(1) as jumlah')
            ->get();
        $list_data_unit_poor = [];
        foreach($res as $v){
            $list_data_unit_poor[$v->unit_label][$v->kualitas] = $v->jumlah;
        }
        $date_range = date('m/d/Y', strtotime($date1)).' - '.date('m/d/Y', strtotime($date2));
        $list_pg = ['PG1'=>'PG1', 'PG2'=>'PG2', 'PG3'=>'PG3'];
        $res = Aktivitas::get(['kode', 'nama']);
        $list_aktivitas = [];
        foreach($res as $v){
            $list_aktivitas[$v->kode] = $v->nama;
        }
        $list_kualitas = ['Excellent'=>'Excellent', 'Very Good'=>'Very Good', 'Good'=>'Good', 'Average' => 'Average', 'Poor', 'Very Poor'];

        return view('home', [
            'list_chart_1a'         => json_encode($list_chart_1a, JSON_UNESCAPED_SLASHES ),
            'list_chart_1b'         => json_encode($list_chart_1b, JSON_UNESCAPED_SLASHES ),
            'list_chart_2'          => json_encode($list_chart_2, JSON_UNESCAPED_SLASHES ),
            'list_data_rk_poor'     => $list_data_rk_poor,
            'list_data_unit_poor'   => $list_data_unit_poor,
            'list_pg'               => $list_pg,
            'date_range'            => $date_range,
            'list_aktivitas'        => $list_aktivitas,
            'list_kualitas'         => $list_kualitas,
            'total_rk'              => $total_rk,
            'total_real'            => $total_real,
            'perc_rk_real'          => $perc_rk_real,
            'pg'                    => $request->pg,
            'aktivitas'             => $request->aktivitas,
            'kualitas'              => $request->kualitas,
        ]);
    }

    public function showDataDashboard(Request $request)
    {
        $user = Auth::user();

        // Handle 'pg' parameter
        if ($request->has('pg')) {
            $pg = $request->pg;
            Cache::put('pg', $request->pg, 15);
        } else {
            if (Cache::has('pg')) {
                $pg = Cache::get('pg');
            } else {
                $pg = ['All'];
            }
        }

        // Handle 'unit' parameter
        if ($request->has('unit')) {
            $unit = $request->unit;
            Cache::put('unit', $request->unit, 15);
        } else {
            if (Cache::has('unit')) {
                $unit = Cache::get('unit');
            } else {
                $unit = ['All'];
            }
        }

        // Calculate yesterday's date
        $yesterday = date('Y-m-d', strtotime('-15 day'));
        $formattedYesterday = date('d F Y', strtotime($yesterday));

        // Card 1 total data aplikasi hari ini
        $result2 = DB::table('report_conformities')
            ->whereDate('tanggal', '=', $yesterday)
            ->select(DB::raw('COUNT(DISTINCT lokasi) as Lokasi_Count'))
            ->whereIn('pg', explode(',', $user->area))
            ->first();

        // Card 2 total unit aktif
        $result1 = DB::table('report_conformities')
            ->whereDate('tanggal', '=', $yesterday)
            ->select(
                DB::raw('COUNT(DISTINCT unit) as Unit_Aktif'),
                DB::raw("'17' as Total_Unit")
            )
            ->whereIn('pg', explode(',', $user->area))
            ->first();

        // Card 4 type application
        $queryResult = DB::table('report_conformities')
            ->whereDate('tanggal', '=', $yesterday)
            ->groupBy('tanggal', 'unit', 'activity')
            ->select(
                'tanggal',
                'unit',
                'activity',
                DB::raw('COUNT(lokasi) AS total_aktivitas')
            )
            ->whereIn('pg', explode(',', $user->area))
            ->get();

        $activityData = [];
        foreach ($queryResult as $item) {
            $key = $item->activity;
            if (!isset($activityData[$key])) {
                $activityData[$key] = [
                    'activity' => $item->activity,
                    'data' => [],
                    'units' => [],
                ];
            }
            $activityData[$key]['data'][] = $item->total_aktivitas;
            $activityData[$key]['units'][] = $item->unit;
        }

        $series = [];
        $labels3 = [];
        $legends = [];

        foreach ($activityData as $activity) {
            $series[] = [
                'name' => implode(', ', $activity['units']),
                'data' => $activity['data'],
            ];
            $labels3[] = $activity['activity'];
            $legends[] = implode(', ', $activity['units']);
        }
        $chartData = [
            'series' => $series,
            'categories' => $labels3,
            'legends' => $legends,
        ];

        $chartDataJSON = json_encode($chartData);

        // Card 5 count unit per location
        $result3 = DB::table('unit AS A')
            ->leftJoin('report_conformities AS B', function ($join) use ($yesterday) {
                $join->on('A.label', '=', 'B.unit')
                    ->whereDate('B.tanggal', '=', $yesterday);
            }) ->groupBy('A.label')
            ->select('A.label', DB::raw('COUNT(B.lokasi)'))
            ->whereIn('A.pg', explode(',', $user->area))
            ->get();

        $labels = [];
        $data = [];
        foreach ($result3 as $item) {
            $labels[] = $item->label;
            $data[] = $item->{'COUNT(B.lokasi)'};
        }

        // Card 3: Total data aplikasi per shift
        $result4 = DB::table('report_conformities')
            ->whereDate('tanggal', '=', $yesterday)
            ->select('shift', DB::raw('COUNT(shift) as shift_count'))
            ->groupBy('shift')
            ->whereIn('pg', explode(',', $user->area))
            ->get();

        $labels2 = [];
        $data2 = [];

        foreach ($result4 as $item) {
            $labels2[] = $item->shift;
            $data2[] = $item->shift_count;
        }

        return view('home', compact('result1', 'result2', 'labels', 'data', 'labels2', 'data2', 'chartDataJSON', 'formattedYesterday', 'pg', 'unit'));
    }


    public function home(){
        return redirect('/');
    }

    public function privacy(){
        return view('privacy', []);
    }

    public function users()
    {
        $param = $_GET;
        $query = User::select();
        $user_data = new GridCenter($query, $param);
    
        echo json_encode($user_data->render(new UserTransformer()));
        exit;
    }

    public function update_rk(Request $request) {
        $logs = Log::where('reference_type', 'rencana_kerja')
            ->where('event', 'update')
            ->whereBetween('created_at', ['2021-12-28 06:00:00', '2021-12-28 08:00:00'])
            ->get();
        foreach($logs AS $log){
            $rk = json_decode($log->object);
            if($rk->status_id==4){
                echo $log->id.' - '.$log->created_at.' - '.$log->created_by.' - '.$rk->id.' - '.$rk->status_id.' - '.$rk->jam_selesai,"<br/>";
            }
        }
    }

    public function check_geofence(Request $request){
        $coordinate = explode(',', $request->coordinate);
        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $lokasi = $geofenceHelper->checkLocation($list_polygon, trim($coordinate[0]), trim($coordinate[1]));
        echo "LOKASI: ".$lokasi."<br/>";
    }

    public function check_lokasi_rk(Request $request){
        $rk = RencanaKerja::find($request->id);
        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $lacak = Lacak::where('ident', $rk->unit_source_device_id)
            ->orderBy('timestamp', 'DESC')
            ->limit(1)
            ->first();
        $position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        $position_longitude        = $lacak != null ? $lacak->position_longitude : 0;
        echo "DEVICE: ".$rk->unit_source_device_id.', GEOLOCATION: '.$position_latitude.' , '.$position_longitude."<br/>";
        $lokasi = $geofenceHelper->checkLocation($list_polygon, $position_latitude, $position_longitude);
        $lokasi = !empty($lokasi) ? substr($lokasi, 0, strlen($lokasi)-2) : '';
        if($lokasi!=$rk->lokasi_kode){
            echo 'Lokasi Anda ['.$lokasi.'] tidak sesuai dengan Lokasi di Rencana Kerja ['.$rk->lokasi_kode.']';
        }
        echo "LOKASI: ".$lokasi."<br/>";
    }

    

    function saveRKS($rencana_kerja_id, $ritase, $grup_aktivitas_id, $aktivitas_id, $nozzle_id, $volume_id, $parameter_id, $realisasi) {
        $nilai_standard = '';
        $bobot = 0;
        $nilai = 0;
        $nilai_bobot = 0;
        $parameter_nama = '';
        $kualitas = '';
        $list_rs = ReportStatus::get();
        if($parameter_id == 999999) {
            $list_rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
                ->whereRaw("(ritase = 999 OR parameter_id = 999)")
                ->get();
            foreach($list_rks as $rks){
                $nilai += $rks->nilai;
                $bobot += $rks->bobot;
                $nilai_bobot += $rks->nilai_bobot;
            }
            $nilai = $nilai / count($list_rks);
            $parameter_nama = 'Total Nilai Kualitas Spraying';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
        } else if($parameter_id == 999) {
            $list_rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
                ->where('ritase', $ritase)
                ->where('parameter_id', '<', 999)
                ->get();
            foreach($list_rks as $rks){
                $bobot += $rks->bobot;
                $nilai_bobot += $rks->nilai_bobot;
            }
            $nilai = round($nilai_bobot / $bobot,2) * 100;
            $parameter_nama = 'Hasil';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
        } else {
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $grup_aktivitas_id)
                ->where('report_parameter_id', $parameter_id)
                ->first();
            $bobot = !empty($rpb->bobot) ? $rpb->bobot : 0;
            $std =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', $parameter_id)
                ->where('report_parameter_standard.aktivitas_id', $aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $nozzle_id)
                ->where('report_parameter_standard.volume_id', $volume_id)
                ->where('d.point', 100)
                ->first(['d.*']);
            $nilai_standard = $std != null ? $std->range_1.' - '.$std->range_2 : '';
            if($std != null) {
                if($std->range_1=='-999') {
                    $nilai_standard = '<= '.$std->range_2;
                } else if($std->range_2=='999') {
                    $nilai_standard = '>= '.$std->range_1;
                } else {
                    $nilai_standard = $std->range_1.' - '.$std->range_2;
                }
            }
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', $parameter_id)
                ->where('report_parameter_standard.aktivitas_id', $aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $nozzle_id)
                ->where('report_parameter_standard.volume_id', $volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if($parameter_id==5){
                    $dt_realisasi = date('Y-m-d '.$realisasi);
                    if($rps->range_1 > $rps->range_2) {
                        $dt_range_1 = date('Y-m-d '.$rps->range_1,strtotime("-1 days"));
                    } else {
                        $dt_range_1 = date('Y-m-d '.$rps->range_1);
                    }
                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                    if($dt_range_1 <= $dt_realisasi && $realisasi <= doubleval($rps->range_2)){
                        $nilai = $rps->point;
                        break;
                    }
                } else {
                    if(doubleval($rps->range_1) <= $realisasi && $realisasi <= doubleval($rps->range_2)){
                        $nilai = $rps->point;
                        break;
                    }
                }
            }
            $nilai_bobot = $nilai / 100 * $bobot;
            $rp = ReportParameter::find($parameter_id);
            $parameter_nama = $rp->nama;
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
        }
        
        echo "BOBOT : ".$bobot.".";
        echo "NILAI : ".$nilai.".";
        echo "NILAIxBOBOT : ".$nilai_bobot."<br/>";

        $rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
            ->where('ritase', $ritase)
            ->where('parameter_id', $parameter_id)
            ->first();
        if($rks==null){
            $rks = new RencanaKerjaSummary;
            $rks->rk_id = $rencana_kerja_id;
            $rks->ritase = $ritase;
            $rks->parameter_id = $parameter_id;
            $rks->parameter_nama = $parameter_nama;
        }
        $rks->standard      = $nilai_standard;
        $rks->realisasi     = $realisasi;
        $rks->nilai         = $nilai;
        $rks->bobot         = $bobot;
        $rks->nilai_bobot   = $nilai_bobot;
        $rks->kualitas      = $kualitas;
        $rks->save();
    }

    public function test(Request $request){
        set_time_limit(0);
        // get Rencana Kerja
        $rk = RencanaKerja::find($request->id);
        $aktivitas = Aktivitas::find($rk->aktivitas_id);
        $list_rs = ReportStatus::get();
        RencanaKerjaSummary::where('rk_id', $rk->id)->delete();
        echo $rk->lokasi_nama.$rk->unit_label.$rk->aktivitas_nama.$rk->nozzle_nama.$rk->volume.' '.$rk->unit_source_device_id."<br/>";

        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);
        $list = Lacak::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
        $is_started = false;
        $waktu_berhenti = 0;
        $ritase = 1;
        $list_movement = [];
        foreach($list AS $k=>$v){
            $lokasi         = $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $waktu_tempuh   = ($k==0) ? 0 : round(abs($v->timestamp - $list[$k-1]->timestamp),2);
            $lebar_kanan    = !empty($v->din_3) && !empty($v->din_1) ? 18 : 0;
            $lebar_kiri     = !empty($v->din_3) && !empty($v->din_2) ? 18 : 0;
            $lebar          = $lebar_kanan + $lebar_kiri;
            $jarak_tempuh   = ($k==0) ? 0 : round(abs($v->vehicle_mileage - $list[$k-1]->vehicle_mileage),3);
            $jarak_spray_kanan  = ($k==0) ? 0 : ($lebar_kanan > 0 ? $jarak_tempuh : 0);
            $jarak_spray_kiri   = ($k==0) ? 0 : ($lebar_kiri > 0 ? $jarak_tempuh : 0);
            if(!empty($lokasi) && $lebar >= 18) {
                $is_started = true;
                $obj = (object) [
                    'timestamp'                 => $v->timestamp,
                    'lokasi'                    => $lokasi,
                    'position_latitude'         => $v->position_latitude,
                    'position_longitude'        => $v->position_longitude,
                    'vehicle_mileage'           => $v->vehicle_mileage,
                    'lebar_kanan'               => $lebar_kanan,
                    'lebar_kiri'                => $lebar_kiri,
                    'lebar'                     => $lebar,
                    'jarak_spray_kanan'         => $jarak_spray_kanan,
                    'jarak_spray_kiri'          => $jarak_spray_kiri,
                ];
                if(array_key_exists($ritase, $list_movement)){
                    $list_movement[$ritase]['list_gps'][] = $obj;
                    $list_movement[$ritase]['jarak_spray_kanan'] += $jarak_spray_kanan;
                    $list_movement[$ritase]['jarak_spray_kiri'] += $jarak_spray_kiri;
                } else {
                    $list_movement[$ritase] = [
                        'list_gps'          => [$obj],
                        'jarak_tempuh'      => 0,
                        'jam_mulai'         => 0,
                        'jam_selesai'       => 0,
                        'waktu_tempuh'      => 0,
                        'kecepatan'         => 0,
                        'jarak_spray_kanan' => $jarak_spray_kanan,
                        'jarak_spray_kiri'  => $jarak_spray_kiri
                    ];
                }
                $waktu_berhenti = 0;

                $point_overlapping = 0;
                if($k>0) {
                    $point_distance = $geofenceHelper->haversineGreatCircleDistance($list[$k-1]->position_latitude, $list[$k-1]->position_longitude, $v->position_latitude, $v->position_longitude);
                    if($point_distance<=4){
                        $point_overlapping = 1;
                    }
                }
            } else {
                $waktu_berhenti += $waktu_tempuh;
            }
            if($is_started && $waktu_berhenti>=240){
                $ritase += 1;
                $is_started = false;
            }
            $rrk = new ReportRencanaKerja;
            $rrk->rencana_kerja_id = $rk->id;
            $rrk->tanggal = $rk->tgl;
            $rrk->shift = $rk->shift_nama;
            $rrk->lokasi = $rk->lokasi_kode;
            $rrk->luas_bruto = $rk->lokasi_lsbruto;
            $rrk->luas_netto = $rk->lokasi_lsnetto;
            $rrk->kode_aktivitas = $rk->aktivitas_kode;
            $rrk->nama_aktivitas = $rk->aktivitas_nama;
            $rrk->nozzle = $rk->nozzle_nama;
            $rrk->volume = $rk->volume;
            $rrk->kode_unit = $rk->unit_id;
            $rrk->nama_unit = $rk->unit_label;
            $rrk->device_id = $rk->unit_source_device_id;
            $rrk->operator = $rk->operator_nama;
            $rrk->driver = $rk->driver_nama;
            $rrk->kasie = $rk->kasie_nama;
            $rrk->status = $rk->status_nama;
            $rrk->jam_mulai = $rk->jam_mulai;
            $rrk->jam_selesai = $rk->jam_selesai;
            $rrk->latitude = $v->position_latitude;
            $rrk->longitude = $v->position_longitude;
            $rrk->position_direction = $v->position_direction;
            $rrk->gsm_signal_level = $v->gsm_signal_level;
            $rrk->timestamp = date('Y-m-d H:i:s', $v->timestamp);
            $rrk->position_speed = $v->position_speed;
            $rrk->din = $v->din;
            $rrk->din_1 = $v->din_1;
            $rrk->din_2 = $v->din_2;
            $rrk->din_3 = $v->din_3;
            $rrk->ritase = $ritase;
            $rrk->overlapping = $point_overlapping;
            $rrk->save();
        }
        $jarak_tempuh_total   = 0;
        $waktu_tempuh_total   = 0;
        $kecepatan_total      = 0;
        $jarak_spray_kanan_total   = 0;
        $jarak_spray_kiri_total   = 0;
        foreach($list_movement as $k=>$v){
            $list_gps = $v['list_gps'];
            echo "RITASE ".$k."<br/>";
            foreach($list_gps as $v2){
                echo "[".date('Y-m-d H:i:s.Z', $v2->timestamp)."] Lokasi: ".$v2->lokasi.", Koordinat: [".$v2->position_latitude.",".$v2->position_longitude."], Mileage: ".$v2->vehicle_mileage.", Jarak Spray Kanan: ".$v2->jarak_spray_kanan.", Jarak Spray Kiri: ".$v2->jarak_spray_kiri.", LEBAR: ".$v2->lebar." <br/>";
            }
            if(count($list_gps)>0){
                $mileage1       = $list_gps[0]->vehicle_mileage;
                $mileage2       = count($list_gps) > 1 ? $list_gps[count($list_gps)-1]->vehicle_mileage : $mileage1;
                $timestamp1     = $list_gps[0]->timestamp;
                $timestamp2     = count($list_gps) > 1 ? $list_gps[count($list_gps)-1]->timestamp : $timestamp1;
                $jarak_tempuh   = round(abs($mileage2 - $mileage1),3);
                $waktu_tempuh   = round(abs($timestamp2 - $timestamp1),2);
                $kecepatan      = $waktu_tempuh > 0 ? round($jarak_tempuh / ($waktu_tempuh/3600),2) : 0;
                $list_movement[$k]['jarak_tempuh']  = $jarak_tempuh;
                $list_movement[$k]['jam_mulai']     = $timestamp1;
                $list_movement[$k]['jam_selesai']   = $timestamp2;
                $list_movement[$k]['waktu_tempuh']  = $waktu_tempuh;
                $list_movement[$k]['kecepatan']     = $kecepatan;
                $jarak_tempuh_total += $jarak_tempuh;
                $waktu_tempuh_total += $waktu_tempuh;
            }
            $stop_time = $k > 1 ? $list_movement[$k]['jam_mulai'] - $list_movement[$k-1]['jam_selesai'] : 0;
            $jarak_spray_kanan_total += $v['jarak_spray_kanan']; 
            $jarak_spray_kiri_total += $v['jarak_spray_kiri']; 
            echo "<hr/>";
            echo "Waktu Tunggu : ".round($stop_time/60,2)." Menit<br/>";
            echo "Jarak Spray : ".$jarak_tempuh." KM<br/>";
            echo "Waktu Spray :".round($waktu_tempuh/60,2)." Menit<br/>";
            echo "Kecepatan Operasi : ".$kecepatan." KM/Jam. ";
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 1, $kecepatan);

            $luas_spray_total = ($v['jarak_spray_kanan'] * 1000 * 18 + $v['jarak_spray_kiri'] * 1000 * 18)/10000;
            echo "Luas Spray : ".$luas_spray_total." Ha <br/>";
            $luas_standard_spray = round(8000 / $rk->volume - 0.012 * (8000 / $rk->volume),2);
            echo "Luas Standard Spray : ".$luas_standard_spray." Ha <br/>";
            $overlapping = ($luas_spray_total / $luas_standard_spray - 1)* 100;
            echo "Overlapping : ".$overlapping." %. ";
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 2, $overlapping);

            echo "Waktu Spray per Ritase :".round($waktu_tempuh/60,2)." Menit. ";
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 3, round($waktu_tempuh/60,2));

            $ketepatan_dosis = 100 - $overlapping;
            echo "Ketepatan Dosis :".$ketepatan_dosis.". ";
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 4, $ketepatan_dosis);

            $golden_time = date('H:i:s', $list_movement[$k]['jam_mulai']);
            echo "Golden Time :".$golden_time.". ";
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 5, $golden_time);

            $wing_level = 1.3;
            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 6, $wing_level);

            $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 999, 0);
            echo "<hr/>";
        } 
        $jam_mulai          = count($list_movement) > 0 ? $list_movement[1]['jam_mulai'] : 0;
        $jam_selesai        = count($list_movement) > 1 ? $list_movement[count($list_movement)]['jam_selesai'] : $jam_mulai;
        $kecepatan_total    = $waktu_tempuh_total > 0 ? round($jarak_tempuh_total / ($waktu_tempuh_total/3600),2) : 0; 
        echo "<br/>";
        // echo "JARAK TEMPUH TOTAL : ".$jarak_tempuh_total." KM<br/>";
        // echo "JAM MULAI :".date('Y-m-d H:i:s.Z', $jam_mulai)."<br/>";
        // echo "JAM SELESAI :".date('Y-m-d H:i:s.Z', $jam_selesai)."<br/>";
        // echo "WAKTU TEMPUH :".$waktu_tempuh_total." Detik<br/>";
        // echo "KECEPATAN : ".$kecepatan_total." KM/Jam<br/>";
        echo "JARAK SPRAY KANAN : ".$jarak_spray_kanan_total." KM<br/>";
        echo "JARAK SPRAY KIRI : ".$jarak_spray_kiri_total." KM<br/>";
        $luas_spray_total = ($jarak_spray_kanan_total * 1000 * 18 + $jarak_spray_kiri_total * 1000 * 18)/10000;
        echo "LUAS SPRAY : ".$luas_spray_total." Ha<br/>";

        $area_not_spray = 0;
        $this->saveRKS($rk->id, 999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 7, $area_not_spray);
        $this->saveRKS($rk->id, 999999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 999999, 0);
        exit;
        $bobot_kecepatan = 0;
        if($kecepatan_total > 6.8){
            $bobot_kecepatan = 50;
        } else if($kecepatan_total < 5.6){
            $bobot_kecepatan = 50;
        } else {
            $bobot_kecepatan = 100;
        }
        $bobot_kecepatan = $bobot_kecepatan / 100 * 30;  
        $golden_time = 0; 
        if(date('H', $jam_mulai) >= '16' || date('H', $jam_mulai) <= '11'){
            $golden_time = 100;
        } else {
            $golden_time = 50;
        }
        $bobot_golden_time = $golden_time / 100 * 15; 
        $data = [
            'jam_mulai'             => $jam_mulai,
            'jam_selesai'           => $jam_selesai,
            'jarak_tempuh'          => $jarak_tempuh_total,
            'waktu_tempuh'          => ($waktu_tempuh_total/60),
            'kecepatan'             => $kecepatan_total,
            'bobot_keceparan'       => $bobot_kecepatan,
            'golden_time'           => $golden_time,
            'bobot_golden_time'     => $bobot_golden_time,
            'list_ritase'           => $list_movement
        ];
        //echo json_encode($data);
        //print_r($data);
        exit;
    }

    public function generate_geofence(Request $request) {
        set_time_limit(0);
        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '2021-12-26 00:00:00';
        $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : '2021-12-27 23:59:59';
        $list_lacak = Lacak::whereRaw("geofence IS NULL")->where('timestamp', '>=', strtotime($jam_mulai))->where('timestamp', '>=', strtotime($jam_mulai))->where('timestamp', '<=', strtotime($jam_selesai))->orderBy('timestamp', 'ASC')->get();
        $list2 = [];
        foreach($list_lacak AS $k=>$v) {
            $geofence = $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->geofence = !empty($geofence) ? substr($geofence, 0, strlen($geofence)-2) : '';
            $v->save();
        }

    }

    public function generate_report_v2(Request $request) {
        // $oldLimit = ini_get( 'memory_limit' );
        // ini_set( 'memory_limit', '-1' );
        // set_time_limit(0);
        $list_rk = RencanaKerja::
            //whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL AND (kualitas IS NULL OR kualitas = '')")
             // whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL")
            where('id', 284)
            ->orderBy('id', 'ASC')
            ->limit(100)
            ->get();
        if(count($list_rk)>0){
            $list_rp = ReportParameter::orderBy('id', 'ASC')->get();
            $list_rs = ReportStatus::get();
            foreach($list_rk AS $rk) {
                $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get()->toArray();
                $kualitas = '';
                if(count($list_rrk)>0) {
                    $aktivitas = Aktivitas::find($rk->aktivitas_id);
                    $list_realisasi = [];
                    foreach($list_rp as $rp){
                        if($rp->id!=2){
                            $list_realisasi[$rp->id] = 0;
                        }
                    }
                    foreach($list_rrk as $k=>$v){
                        foreach($list_rp as $rp){
                            if($rp->id!=2){
                                $list_realisasi[$rp->id] += $v['parameter_'.$rp->id];
                            }
                        }
                    }
                    foreach($list_rp as $rp){
                        if($rp->id==2){
                            $list_realisasi[$rp->id] = $list_rrk[0]['parameter_'.$rp->id];
                        } else {
                            $list_realisasi[$rp->id] = $list_realisasi[$rp->id] / count($list_rrk);
                        }
                    }
                    $total_poin = 0;
                    foreach($list_rp as $rp){
                        $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                            ->where('d.report_parameter_id', $rp->id)
                            ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                            ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                            ->where('report_parameter_standard.volume_id', $rk->volume_id)
                            ->orderByRaw("d.range_1*1 ASC")
                            ->get(['d.*']);
                        $standard = '';
                        foreach($list_rps AS $rps){
                            if($rps->point==1){
                                $standard = $rps->range_1.' - '.$rps->range_2;
                            }
                        }
                        $realisasi = $list_realisasi[$rp->id];
                        $nilai = 0;
                        if($rp->id==2){
                            foreach($list_rps AS $rps){
                                $dt_nilai = date('Y-m-d '.$nilai);
                                $dt_range_1 = date('Y-m-d '.$rps->range_1);
                                if($rps->range_1 > $rps->range_2) {
                                    if($dt_nilai < $dt_range_1){
                                        $dt_nilai = date('Y-m-d '.$nilai,strtotime("+1 days"));
                                    }
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2,strtotime("+1 days"));
                                } else {
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                                }
                                if($dt_range_1 <= $dt_nilai && $dt_nilai <= $dt_range_2){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        } else {
                            foreach($list_rps AS $rps){
                                if(doubleval($rps->range_1) <= $realisasi && $realisasi <= doubleval($rps->range_2)){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        }
                        $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_UNIT')->first(['value']);
                        $offline_unit = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                        if(in_array($rk->unit_id, $offline_unit)){
                            $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_BOBOT')->first(['value']);
                            $offline_bobot = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                            $bobot = !empty($offline_bobot[$rp->id-1]) ? $offline_bobot[$rp->id-1] : 0;
                        } else {
                            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                                ->where('report_parameter_id', $rp->id)
                                ->first();
                            $bobot = !empty($rpb->bobot) ? $rpb->bobot : 0;
                        }
                        $poin = $nilai * $bobot;
                        echo "STANDARD: ".$standard.", REALISASI: ".$realisasi.", NILAI: ".$nilai.", BOBOT: ".$bobot.", POIN: ".$poin."<br/>";
                        $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                            ->where('ritase', 999)
                            ->where('parameter_id', $rp->id)
                            ->first();
                        if($rks==null){
                            $rks = new RencanaKerjaSummary;
                            $rks->rk_id = $rk->id;
                            $rks->ritase = 999;
                            $rks->parameter_id = $rp->id;
                            $rks->parameter_nama = $rp->nama;
                        }
                        $rks->standard      = $standard;
                        $rks->realisasi     = $realisasi;
                        $rks->nilai         = $nilai;
                        $rks->bobot         = $bobot;
                        $rks->nilai_bobot   = $poin;
                        $rks->kualitas      = null;
                        $rks->save();
                        $total_poin += $poin;
                    }
                    $kualitas = '';
                    foreach($list_rs as $v){
                        if(doubleval($v->range_1) <= $total_poin && $total_poin <= doubleval($v->range_2)){
                            $kualitas = $v->status;
                            break;
                        }
                    }
                    echo "KUALITAS: ".$kualitas;
                    $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                        ->where('ritase', 999999)
                        ->where('parameter_id', 999)
                        ->first();
                    if($rks==null){
                        $rks = new RencanaKerjaSummary;
                        $rks->rk_id = $rk->id;
                        $rks->ritase = 999999;
                        $rks->parameter_id = 999;
                        $rks->parameter_nama = 'Total';
                    }
                    $rks->standard      = null;
                    $rks->realisasi     = null;
                    $rks->nilai         = null;
                    $rks->bobot         = null;
                    $rks->nilai_bobot   = $total_poin;
                    $rks->kualitas      = $kualitas;
                    $rks->save();
                } else {
                    $kualitas = '-';
                }
                $rk->kualitas = $kualitas;
                $rk->jam_laporan2 = date('Y-m-d H:i:s');
                $rk->save();
            } 
        }
        // ini_set( 'memory_limit', $oldLimit );
        // set_time_limit(0);
        // $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '2021-12-27 00:00:00';
        // $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : '2021-12-27 23:59:59';
        // $list_lacak = Lacak::where('timestamp', '>=', strtotime($jam_mulai))->where('timestamp', '<=', strtotime($jam_selesai))->orderBy('timestamp', 'ASC')->get();
        // $list_key = [];
        // $list2 = [];
        // foreach($list_lacak AS $k=>$v) {
        //     $v->is_spraying = !empty($v->din_3) && (!empty($v->din_1) || !empty($v->din_2)) ? 'Y' : 'N';
        //     $key2 = $v->ident.'|'.$v->geofence;
        //     if($k>0) {
        //         $key1 = $list_lacak[$k-1]->ident.'|'.$list_lacak[$k-1]->geofence;
        //         if($key1!=$key2) {
        //             if(array_key_exists($key2, $list_key)) {
        //                 $list_key[$key2] += 1;
        //             } else {
        //                 $list_key[$key2] = 0;
        //             }
        //         }
        //     } else {
        //         $list_key[$key2] = 0;
        //     }
        //     $key =  $key2.'|'.$list_key[$key2];
        //     if(array_key_exists($key, $list2)) {
        //         $list2[$key][] = $v;
        //     } else {
        //         $list2[$key] = [$v];
        //     }
        // }

        // foreach($list2 AS $k=>$v) {
        //     $key = explode('|', $k);
        //     $unit_source_device_id = $key[0];
        //     if($unit_source_device_id!='867648047208531') continue;
        //     $geofence = $key[1];
        //     $seq = $key[2];
        //     $unit = Unit::where('source_device_id', $unit_source_device_id)->first();
        //     $tgl = '2021-12-27';
        //     $rencana_kerja = RencanaKerja::where('tgl', $tgl)
        //         ->where('unit_source_device_id', $unit_source_device_id)
        //         ->where('lokasi_kode', $geofence)
        //         ->first();
        //     $rencana_kerja_id = $rencana_kerja == null ? '*NOTFOUND*' : $rencana_kerja->id;
        //     //echo 'UNIT '.$unit->label.'['.$unit->source_device_id."] di ".$geofence.$seq.", RK#".$rencana_kerja_id." : <br/>";
        //     foreach($v AS $k2=>$v2) {
        //         echo date('d-m-Y H:i:s', $v2->timestamp).', UNIT: '.$unit->label.', GEOFENCE: '.$geofence.', SPRAYING : '.$v2->is_spraying.", RKID: ".$rencana_kerja_id."<br/>";
        //     }
        // }

    }

    public function generate_report(Request $request){
        set_time_limit(0);
        $geofenceHelper = new GeofenceHelper;
        $list_rk = RencanaKerja::
            //whereRaw("status_id = 4 AND (jam_laporan IS NULL OR jam_laporan = '')")
            where('id', $request->id)
            ->orderBy('id', 'ASC')
            ->get();
        foreach($list_rk AS $rk) {
            ReportRencanaKerja::where('rencana_kerja_id', $rk->id)->delete();
            $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);
            $list = Lacak::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
            $list2 = [];
            $i2 = 0;
            $list_kel = [];
            foreach($list AS $k=>$v) {
                $lokasi             = $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
                $v->waktu_tempuh    = ($k==0) ? 0 : round(abs($v->timestamp - $list[$k-1]->timestamp),2);
                $v->spraying      = !empty($lokasi) && $v->position_speed >= 1 && !empty($v->din_3) && (!empty($v->din_1) || !empty($v->din_2)) ? 'Y' : 'N';
                if($k>0 && $v->spraying != $list2[$k-1]->spraying) {
                    $i2++;
                }
                if(array_key_exists($i2, $list_kel)) {
                    $list_kel[$i2]->selesai        = $v->timestamp;
                    $list_kel[$i2]->waktu_tempuh   = round(abs($list_kel[$i2]->selesai - $list_kel[$i2]->mulai),2);
                    $list_kel[$i2]->waktu_tempuh2  += $v->waktu_tempuh;
                    $list_kel[$i2]->break          = $list_kel[$i2]->spraying == 'N' && $list_kel[$i2]->waktu_tempuh > 240 ? 'Y' : 'N';
                } else {
                    $list_kel[$i2] = (object) [
                        'spraying'      => $v->spraying, 
                        'mulai'         => $v->timestamp, 
                        'selesai'       => $v->timestamp, 
                        'waktu_tempuh'  => 0, 
                        'waktu_tempuh2' => $v->waktu_tempuh,
                        'break'         => 'N'
                    ];
                }
                $v->kel = $i2;
                $list2[] = $v;
            }
            $ritase = 0;
            foreach($list2 as $k=>$v){
                if($k>0){
                    if($v->spraying=='Y' && ($list_kel[$list2[$k-1]->kel]->break=='Y' || $ritase==0)) {
                        $ritase++;
                    }
                } else {
                    if($v->spraying=='Y') {
                        $ritase++;
                    }
                }
                if($list_kel[$v->kel]->break=='Y'){
                    $v->ritase = 0;
                } else {
                    $v->ritase = $ritase;
                }

                $is_overlap = 0;
                $overlapped_area = [];
                foreach($list as $key=>$point) {
                    if($k<2) break;
                    if($key>=($k-1)) break;
                    $jarak = $geofenceHelper->haversineGreatCircleDistance($point->position_latitude, $point->position_longitude, $v->position_latitude, $v->position_longitude);
                    if($jarak<=18 && $v->spraying=='Y'){
                        $overlapped_area[] = $point->position_latitude.','.$point->position_longitude.'('.$jarak.' m) @ '.date('Y-m-d H:i:s', $point->timestamp);
                        $is_overlap = 1;
                    }
                }
                echo 'TIME: '.date('Y-m-d H:i:s', $v->timestamp).', LOCATION: '.$v->position_latitude.','.$v->position_longitude.' SPARYING: '.$v->spraying.', OVERLAPPING: '.$is_overlap.', OVERLAPPED AREA: ['.implode('|',$overlapped_area)."] <br/>";
                $rrk = new ReportRencanaKerja;
                $rrk->rencana_kerja_id = $rk->id;
                $rrk->tanggal = $rk->tgl;
                $rrk->shift = $rk->shift_nama;
                $rrk->lokasi = $rk->lokasi_kode;
                $rrk->luas_bruto = $rk->lokasi_lsbruto;
                $rrk->luas_netto = $rk->lokasi_lsnetto;
                $rrk->kode_aktivitas = $rk->aktivitas_kode;
                $rrk->nama_aktivitas = $rk->aktivitas_nama;
                $rrk->nozzle = $rk->nozzle_nama;
                $rrk->volume = $rk->volume;
                $rrk->kode_unit = $rk->unit_id;
                $rrk->nama_unit = $rk->unit_label;
                $rrk->device_id = $rk->unit_source_device_id;
                $rrk->operator = $rk->operator_nama;
                $rrk->driver = $rk->driver_nama;
                $rrk->kasie = $rk->kasie_nama;
                $rrk->status = $rk->status_nama;
                $rrk->jam_mulai = $rk->jam_mulai;
                $rrk->jam_selesai = $rk->jam_selesai;
                $rrk->latitude = $v->position_latitude;
                $rrk->longitude = $v->position_longitude;
                $rrk->position_direction = $v->position_direction;
                $rrk->gsm_signal_level = $v->gsm_signal_level;
                $rrk->timestamp = date('Y-m-d H:i:s', $v->timestamp);
                $rrk->position_speed = $v->position_speed;
                $rrk->din = $v->din;
                $rrk->din_1 = $v->din_1;
                $rrk->din_2 = $v->din_2;
                $rrk->din_3 = $v->din_3;
                $rrk->ritase = $v->ritase;
                $rrk->overlapping = $is_overlap;
                $rrk->save();
            }
        }

        // GENERATE SUMMARY
        $rk = RencanaKerja::find($request->id);
        $aktivitas = Aktivitas::find($rk->aktivitas_id);
        $list = VReportRencanaKerja::where('rencana_kerja_id', $request->id)->get();
        if(count($list)>0){
            echo "<table border=1>";
            echo "<thead>";
            echo "<tr>";
            echo "<td>RITASE</td>";
            echo "<td>KECEPATAN OEPRASI</td>";
            echo "<td>GOLDEN TIME</td>";
            echo "<td>WAKTU SPRAY PER RITASE</td>";
            echo "<td></td>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            $kecepatan_operasi = 0;
            $waktu_spray_per_ritase = 0;
            foreach($list as $v){
                echo "<tr>";
                echo "<td>".$v->ritase."</td>";
                echo "<td>".$v->kecepatan_operasi."</td>";
                echo "<td>".$v->golden_time."</td>";
                echo "<td>".$v->waktu_spray_per_ritase."</td>";
                echo "<td></td>";
                echo "</tr>";
                $kecepatan_operasi += $v->kecepatan_operasi;
                $waktu_spray_per_ritase += $v->waktu_spray_per_ritase;
            }
            $golden_time = $list[0]->golden_time;
            $kecepatan_operasi = $kecepatan_operasi / count($list);
            $waktu_spray_per_ritase = $waktu_spray_per_ritase / count($list);

            $poin_kecepatan_operasi = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 1)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if(doubleval($rps->range_1) <= $kecepatan_operasi && $kecepatan_operasi <= doubleval($rps->range_2)){
                    $poin_kecepatan_operasi = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 1)
                ->first();
            $poin_kecepatan_operasi = !empty($rpb->bobot) ? $poin_kecepatan_operasi * $rpb->bobot : 0;

            $poin_golden_time = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 2)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                $dt_golden_time = date('Y-m-d '.$golden_time);
                echo "GOLDEN TIME : ".$dt_golden_time."<br/>";
                if($rps->range_1 > $rps->range_2) {
                    $dt_range_2 = date('Y-m-d '.$rps->range_2,strtotime("+1 days"));
                } else {
                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                }
                $dt_range_1 = date('Y-m-d '.$rps->range_1);
                echo "RANGE : ".$dt_range_1." - ".$dt_range_2."<br/>";
                if($dt_range_1 <= $dt_golden_time && $dt_golden_time <= $dt_range_2){
                    $poin_golden_time = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 2)
                ->first();
            $poin_golden_time = !empty($rpb->bobot) ? $poin_golden_time * $rpb->bobot : 0;
            $poin_waktu_spray_per_ritase = 0;
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 3)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if(doubleval($rps->range_1) <= $waktu_spray_per_ritase && $waktu_spray_per_ritase <= doubleval($rps->range_2)){
                    $poin_waktu_spray_per_ritase = $rps->point;
                    break;
                }
            }
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->where('report_parameter_id', 3)
                ->first();
            $poin_waktu_spray_per_ritase = !empty($rpb->bobot) ? $poin_waktu_spray_per_ritase * $rpb->bobot : 0;
            $total_poin = $poin_kecepatan_operasi+$poin_golden_time+$poin_waktu_spray_per_ritase;

            $list_rs = ReportStatus::get();
            $kualitas = '';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $total_poin && $total_poin <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }

            echo "<tr>";
            echo "<td>RATA-RATA</td>";
            echo "<td>".$kecepatan_operasi."</td>";
            echo "<td>".$golden_time."</td>";
            echo "<td>".$waktu_spray_per_ritase."</td>";
            echo "<td></td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td>POIN</td>";
            echo "<td>".$poin_kecepatan_operasi."</td>";
            echo "<td>".$poin_golden_time."</td>";
            echo "<td>".$poin_waktu_spray_per_ritase."</td>";
            echo "<td>".$total_poin."</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td colspan=4>KATEGORI</td>";
            echo "<td>".$kualitas."</td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "NOT FOUND";
        }
    } 

    public function phpinfo(){
        phpinfo();
    }

    protected function guard(){
        return Auth::guard('web');
    }

}

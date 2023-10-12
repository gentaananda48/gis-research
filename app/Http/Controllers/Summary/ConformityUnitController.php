<?php
namespace App\Http\Controllers\Summary;

use App\Http\Controllers\Controller;
use App\Model\Aktivitas;
use App\Model\RencanaKerja;
use App\Model\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConformityUnitController extends Controller {
    public function index(Request $request){
        $user = $this->guard()->user();
        $list_pg = explode(',', $user->area);
        $units = Unit::get()->pluck('label', 'id');

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

        return view('summary.conformity_unit.index', [
            'list_chart_1a'         => json_encode($list_chart_1a, JSON_UNESCAPED_SLASHES ),
            'list_chart_1b'         => json_encode($list_chart_1b, JSON_UNESCAPED_SLASHES ),
            'list_chart_2'          => json_encode($list_chart_2, JSON_UNESCAPED_SLASHES ),
            'list_data_rk_poor'     => $list_data_rk_poor,
            'list_data_unit_poor'   => $list_data_unit_poor,
            'list_pg'               => $list_pg,
            'date_range'            => $date_range,
            'total_rk'              => $total_rk,
            'total_real'            => $total_real,
            'perc_rk_real'          => $perc_rk_real,
            'pg'                    => $request->pg,
            'unit'                  => $request->unit,
            'units'                 => $units
        ]);
    }

    protected function guard(){
        return Auth::guard('web');
    }
}
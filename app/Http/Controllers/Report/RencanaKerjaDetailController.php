<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Model\VRencanaKerjaDetail;
use App\Model\RencanaKerjaSummary;
use App\Model\Lokasi;
use App\Model\Shift;
use App\Model\Aktivitas;
use App\Model\Unit;
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Model\ReportStatus;
use App\Center\GridCenter;
use App\Transformer\ReportRencanaKerjaDetailTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportRencanaKerjaDetailExport;

class RencanaKerjaDetailController extends Controller {
    public function index() {
        $list_shift = [];
        $list_lokasi = [];
        $list_aktivitas = [];
        $list_unit = [];
        $list_nozzle = [];
        $list_volume = [];
        $list_status = [];
        $list_report_status = [];
        $res = Shift::get(['id', 'nama']);
        foreach($res AS $v){
            $list_shift[$v->id] = $v->nama;
        }
        $res = Lokasi::get(['kode', 'nama']);
        foreach($res AS $v){
            $list_lokasi[$v->kode] = $v->nama;
        }
        $res = Aktivitas::get(['kode', 'nama']);
        foreach($res AS $v){
            $list_aktivitas[$v->kode] = $v->nama;
        }
        $res = Unit::get(['id', 'label']);
        foreach($res AS $v){
            $list_unit[$v->id] = $v->label;
        }
        $res = Nozzle::get(['id', 'nama']);
        foreach($res AS $v){
            $list_nozzle[$v->id] = $v->nama;
        }
        $res = VolumeAir::get(['volume']);
        foreach($res AS $v){
            $list_volume[$v->volume] = $v->volume;
        }
        $res = ReportStatus::get(['id', 'status']);
        foreach($res AS $v){
            $list_report_status[$v->status] = $v->status;
        }
        return view('report.rencana_kerja_detail.index', [
            'list_shift'        => $list_shift,
            'list_lokasi'       => $list_lokasi,
            'list_aktivitas'    => $list_aktivitas,
            'list_unit'         => $list_unit,
            'list_nozzle'       => $list_nozzle,
            'list_volume'       => $list_volume,
            'list_report_status'    => $list_report_status
        ]);
    }

    public function get_list(Request $request){
        $user = $this->guard()->user();
        $kasie_id = $user->id;
        $query = RencanaKerjaSummary::select(['rencana_kerja_summary.*'])
            ->join('rencana_kerja As rk', 'rk.id', '=', 'rencana_kerja_summary.rk_id')
            //where('kasie_id', $kasie_id)
           	->whereIn('rk.lokasi_grup', explode(',', $user->area))
            ->orderBy('rk_id', 'ASC')
            ->orderBy('ritase', 'ASC')
            ->orderBy('parameter_id', 'ASC');
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d', strtotime($tgl[1]));
            $query->whereBetween('rk.tgl', [$tgl_1, $tgl_2]);
        }
        if(isset($request->shift)){
            $query->whereIn('rk.shift_id', $request->shift);
        }
        if(isset($request->lokasi)){
            $query->whereIn('rk.lokasi_kode', $request->lokasi);
        }
        if(isset($request->aktivitas)){
            $query->whereIn('rk.aktivitas_kode', $request->aktivitas);
        }
        if(isset($request->unit)){
            $query->whereIn('rk.unit_id', $request->unit);
        }
        if(isset($request->nozzle)){
            $query->whereIn('rk.nozzle_id', $request->nozzle);
        }
        if(isset($request->volume)){
            $query->whereIn('rk.volume', $request->volume);
        }
        if(isset($request->kualitas)){
            $query->whereIn('rk.kualitas', $request->kualitas);
        }
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportRencanaKerjaDetailTransformer()));
        exit;
    }

    public function export(Request $request){
        $user = $this->guard()->user();
        return Excel::download(new ReportRencanaKerjaDetailExport($request, $user->id), 'report_rencana_kerja_detail.xlsx');
    }

    protected function guard(){
        return Auth::guard('web');
    }
}

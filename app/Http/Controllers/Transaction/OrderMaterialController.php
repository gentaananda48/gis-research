<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaBahan;
use App\Model\RencanaKerjaLog;
use App\Model\Shift;
use App\Model\Lokasi;
use App\Model\Aktivitas;
use App\Model\Unit;
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Model\Status;
use App\Center\GridCenter;
use App\Transformer\RencanaKerjaTransformer;

class OrderMaterialController extends Controller {
    public function index() {
        $list_shift = [];
        $list_lokasi = [];
        $list_aktivitas = [];
        $list_unit = [];
        $list_nozzle = [];
        $list_volume = [];
        $list_status = [];
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
        $res = Status::where('proses', 'RENCANA_KERJA')->get(['id', 'nama']);
        foreach($res AS $v){
            $list_status[$v->id] = $v->nama;
        }
        return view('transaction.order_material.index', [
            'list_shift'        => $list_shift,
            'list_lokasi'       => $list_lokasi,
            'list_aktivitas'    => $list_aktivitas,
            'list_unit'         => $list_unit,
            'list_nozzle'       => $list_nozzle,
            'list_volume'       => $list_volume,
            'list_status'       => $list_status
        ]);
    }

    public function get_list(Request $request){
        $user = $this->guard()->user();
        $kasie_id = $user->id;
        $query = RencanaKerja:://where('kasie_id', $kasie_id)->
            whereIn('lokasi_grup', explode(',', $user->area));
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d', strtotime($tgl[1]));
            $query->whereBetween('tanggal', [$tgl_1, $tgl_2]);
        }
        if(isset($request->shift)){
            $query->whereIn('shift_id', $request->shift);
        }
        if(isset($request->lokasi)){
            $query->whereIn('lokasi_kode', $request->lokasi);
        }
        if(isset($request->aktivitas)){
            $query->whereIn('aktivitas_kode', $request->aktivitas);
        }
        if(isset($request->unit)){
            $query->whereIn('unit_id', $request->unit);
        }
        if(isset($request->nozzle)){
            $query->whereIn('nozzle_id', $request->nozzle);
        }
        if(isset($request->volume)){
            $query->whereIn('volume', $request->volume);
        }
        if(isset($request->status)){
            $query->whereIn('om_status_id', $request->status);
        }
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new RencanaKerjaTransformer()));
        exit;
    }

    public function show($id) {
        $data = RencanaKerja::find($id);
        $bahan = RencanaKerjaBahan::where('rk_id', $id)->get();
        $data->bahan = $bahan;
        return view('transaction.order_material.show', ['data' => $data]);
    }

    protected function guard(){
        return Auth::guard('web');
    }

}

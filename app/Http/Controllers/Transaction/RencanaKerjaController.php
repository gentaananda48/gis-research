<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Model\RencanaKerja;
use App\Model\Lokasi;
use App\Model\Shift;
use App\Model\Aktivitas;
use App\Model\Unit;
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Model\Status;
use App\Model\KoordinatLokasi;
use App\Model\Lacak;
use App\Center\GridCenter;
use App\Transformer\RencanaKerjaTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RencanaKerjaImport;
use App\Exports\RencanaKerjaExport;
use App\Helper\GeofenceHelper;

class RencanaKerjaController extends Controller {
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
        return view('transaction.rencana_kerja.index', [
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
            $query->whereBetween('tgl', [$tgl_1, $tgl_2]);
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
            $query->whereIn('status_id', $request->status);
        }
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new RencanaKerjaTransformer()));
        exit;
    }

    public function export(Request $request){
        $user = $this->guard()->user();
        return Excel::download(new RencanaKerjaExport($request, $user->id), 'rencana_kerja.xlsx');
    }

    public function show($id) {
        $data = RencanaKerja::find($id);
        return view('transaction.rencana_kerja.edit', ['data' => $data]);
    }

    public function import(Request $request) {
        $user = $this->guard()->user();
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $import = Excel::import(new RencanaKerjaImport($user->id), request()->file('file'));
        if($import) {
            //redirect
            return redirect()->route('transaction.rencana_kerja')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            //redirect
            return redirect()->route('transaction.rencana_kerja')->with(['error' => 'Data Gagal Diimport!']);
        }
    }

    public function playback(Request $request, $id) {
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($rk->unit_id);
        $list_interval = [];
        for($i=1; $i<=10; $i++){
            $list_interval[$i*100] = ($i/10).' Detik';
        }
        $list = KoordinatLokasi::orderBy('lokasi', 'ASC')
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $geofenceHelper = new GeofenceHelper;
        $durasi = strtotime($jam_selesai) - strtotime($jam_mulai) + 1;
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp', 'din_1', 'din_2', 'din_3']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return view('transaction.rencana_kerja.playback', [
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
    }

    protected function guard(){
        return Auth::guard('web');
    }
}

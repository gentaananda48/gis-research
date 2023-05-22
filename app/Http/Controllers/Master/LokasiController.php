<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Lokasi;
use App\Model\PG;
use App\Model\KoordinatLokasi;
use App\Center\GridCenter;
use App\Transformer\LokasiTransformer;
use App\Transformer\KoordinatLokasiTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LokasiImport;

class LokasiController extends Controller {
    public function index() {
        return view('master.lokasi.index');
    }

    public function get_list(Request $request) {
        $query = Lokasi::select();
        if(!empty($request->kode)){
            $query->whereRaw("kode LIKE '%".$request->kode."%'");
        }
        if(!empty($request->nama)){
            $query->whereRaw("nama LIKE '%".$request->nama."%'");
        }
        if(!empty($request->grup)){
            $query->whereRaw("grup LIKE '%".$request->grup."%'");
        }
        if(!empty($request->wilayah)){
            $query->whereRaw("wilayah LIKE '%".$request->wilayah."%'");
        }
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new LokasiTransformer()));
        exit;
    }

    public function create() {
        $list_grup = [];
        $res = PG::get(['nama']);
        foreach($res as $v) {
            $list_grup[$v->nama] = $v->nama;
        }
        return view('master.lokasi.create', ['list_grup' => $list_grup]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Lokasi::where('kode', '=', $request->kode)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $lokasi= new Lokasi;   
            $lokasi->kode       = $request->input('kode'); 
            $lokasi->nama       = $request->input('nama'); 
            $lokasi->grup       = $request->input('grup'); 
            $lokasi->lsbruto    = $request->input('lsbruto'); 
            $lokasi->lsnetto    = $request->input('lsnetto');
            $lokasi->status     = $request->input('status');  
            $lokasi->map_topleft        = $request->input('map_topleft'); 
            $lokasi->map_bottomright    = $request->input('map_bottomright'); 
            $lokasi->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/lokasi')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = Lokasi::find($id);
        $list_grup = [];
        $res = PG::get(['nama']);
        foreach($res as $v) {
            $list_grup[$v->nama] = $v->nama;
        }
        return view('master.lokasi.edit', ['list_grup' => $list_grup, 'data' => $data]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['kode' => 'required', 'nama' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Lokasi::where('kode', '=', $request->kode)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $lokasi= Lokasi::find($id);;   
            $lokasi->kode       = $request->input('kode'); 
            $lokasi->nama       = $request->input('nama'); 
            $lokasi->grup       = $request->input('grup'); 
            $lokasi->lsbruto    = $request->input('lsbruto'); 
            $lokasi->lsnetto    = $request->input('lsnetto');
            $lokasi->status     = $request->input('status');  
            $lokasi->map_topleft        = $request->input('map_topleft'); 
            $lokasi->map_bottomright    = $request->input('map_bottomright'); 
            $lokasi->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/lokasi')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try {
            $lokasi = Lokasi::find($id);
            $lokasi->delete();
            return redirect('master/lokasi')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function import_lokasi(Request $request) {
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);
        $prev_memory_limit = ini_get('memory_limit');
        $prev_max_execution_time = ini_get('max_execution_time');
        ini_set('memory_limit', '-1' );
        ini_set('max_execution_time', 0);
        $import = Excel::import(new LokasiImport,request()->file('file'));
        ini_set('memory_limit', $prev_memory_limit);
        ini_set('max_execution_time', $prev_max_execution_time);
        if($import) {
            //redirect
            return redirect()->route('master.lokasi')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            //redirect
            return redirect()->route('master.lokasi')->with(['error' => 'Data Gagal Diimport!']);
        }
    }

    public function map($id) {
        $data = Lokasi::find($id);
        $koordinat = KoordinatLokasi::where('lokasi', $data->kode)->orderBy('posnr','asc')->get(['long as lng', 'latd as lat']);
        return view('master.lokasi.map', [
            'data'          => $data,
            'koordinat'     => $koordinat
        ]);
    }

    public function koordinat($id) {
        $lokasi = Lokasi::find($id);
        return view('master.lokasi.koordinat', [
            'lokasi'       => $lokasi
        ]);
    }

    public function koordinat_get_list(Request $request, $id) {
        $lokasi = Lokasi::find($id);
        $query = KoordinatLokasi::where('lokasi', $lokasi->kode)->orderBy('bagian','asc')->orderBy('posnr','asc')->select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new KoordinatLokasiTransformer()));
        exit;
    }
}

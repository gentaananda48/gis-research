<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Lokasi;
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

    public function get_list(Request $request){
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

    public function create()
    {
        return view('master.lokasi.create', []);
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
            $lokasi->lsbruto    = $request->input('lsbruto'); 
            $lokasi->lsnetto    = $request->input('lsnetto');
            $lokasi->status     = $request->input('status');  
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
        return view('master.lokasi.edit', ['data' => $data]);

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
            $lokasi->lsbruto    = $request->input('lsbruto'); 
            $lokasi->lsnetto    = $request->input('lsnetto');
            $lokasi->status     = $request->input('status'); 
            $lokasi->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/lokasi')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $lokasi = Lokasi::find($id);
            $lokasi->delete();
            return redirect('master/lokasi')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function import_lokasi(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $import = Excel::import(new LokasiImport,request()->file('file'));
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

    public function koordinat_get_list(Request $request, $id){
        $lokasi = Lokasi::find($id);
        $query = KoordinatLokasi::where('lokasi', $lokasi->kode)->orderBy('bagian','asc')->orderBy('posnr','asc')->select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new KoordinatLokasiTransformer()));
        exit;
    }
}

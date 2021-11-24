<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Bahan;
use App\Model\Aktivitas;
use App\Center\GridCenter;
use App\Transformer\BahanTransformer;

class BahanController extends Controller {
    public function index() {
        return view('master.bahan.index');
    }

    public function get_list(){
        $query = Bahan::selectRaw("bahan.*,(SELECT GROUP_CONCAT(nama) FROM aktivitas WHERE FIND_IN_SET(id,bahan.kategori)>0) kategori_nama");
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new BahanTransformer()));
        exit;
    }

    public function create()
    {
        $res = Aktivitas::get(['id', 'nama']);
        $list_kategori = [];
        foreach($res AS $v){
            $list_kategori[$v->id] = $v->nama;
        }
        return view('master.bahan.create', ['list_kategori' => $list_kategori]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'kode' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Bahan::where('kode', '=', $request->kode)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $bahan= new Bahan;   
            $bahan->kode        = $request->input('kode'); 
            $bahan->nama        = $request->input('nama'); 
            $bahan->kategori    = implode(",",$request->input('kategori')); 
            $bahan->uom         = $request->input('uom'); 
            $bahan->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/bahan')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = Bahan::find($id);
        $res = Aktivitas::get(['id', 'nama']);
        $list_kategori = [];
        foreach($res AS $v){
            $list_kategori[$v->id] = $v->nama;
        }
        return view('master.bahan.edit', ['data' => $data, 'list_kategori' => $list_kategori]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['kode' => 'required', 'kode' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Bahan::where('kode', '=', $request->kode)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $bahan= Bahan::find($id);;   
            $bahan->kode        = $request->input('kode'); 
            $bahan->nama        = $request->input('nama'); 
            $bahan->kategori    = implode(",",$request->input('kategori')); 
            $bahan->uom         = $request->input('uom'); 
            $bahan->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/bahan')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $bahan = Bahan::find($id);
            $bahan->delete();
            return redirect('master/bahan')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

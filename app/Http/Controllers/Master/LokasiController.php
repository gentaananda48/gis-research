<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Lokasi;
use App\Center\GridCenter;
use App\Transformer\LokasiTransformer;

class LokasiController extends Controller {
    public function index() {
        return view('master.lokasi.index');
    }

    public function getList(){
        $query = Lokasi::select();
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
        $validated_fields = ['kode' => 'required', 'status' => 'required'];
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
        $validated_fields = ['kode' => 'required', 'status' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Lokasi::where('kode', '=', $request->lokasi)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->lokasi . " already exist!");
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
}

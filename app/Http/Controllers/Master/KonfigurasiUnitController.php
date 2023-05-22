<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\KonfigurasiUnit;
use App\Model\Unit;
use App\Center\GridCenter;
use App\Transformer\KonfigurasiUnitTransformer;

class KonfigurasiUnitController extends Controller {
    public function index() {
        return view('master.konfigurasi_unit.index');
    }

    public function get_list(){
        $query = KonfigurasiUnit::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new KonfigurasiUnitTransformer()));
        exit;
    }

    public function create(){
        $res = Unit::get(['label']);
        $list_unit = [];
        foreach($res AS $v){
            $list_unit[$v->label] = $v->label;
        }
        return view('master.konfigurasi_unit.create', ['list_unit' => $list_unit]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['unit' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = KonfigurasiUnit::where('unit', '=', $request->unit)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->unit . " already exist!");
        }
        try {
            $konfigurasiunit = new KonfigurasiUnit;   
            $konfigurasiunit->unit 					= $request->unit; 
            $konfigurasiunit->debit_kiri 			= $request->debit_kiri; 
            $konfigurasiunit->debit_kanan 			= $request->debit_kanan; 
            $konfigurasiunit->koefisien_sayap_kiri 	= $request->koefisien_sayap_kiri; 
            $konfigurasiunit->koefisien_sayap_kanan = $request->koefisien_sayap_kanan; 
            $konfigurasiunit->minimum_spray_kiri 	= $request->minimum_spray_kiri; 
            $konfigurasiunit->minimum_spray_kanan 	= $request->minimum_spray_kanan; 
            $konfigurasiunit->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/konfigurasi_unit')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = KonfigurasiUnit::find($id);
        $res = Unit::get(['label']);
        $list_unit = [];
        foreach($res AS $v){
            $list_unit[$v->label] = $v->label;
        }
        return view('master.konfigurasi_unit.edit', ['data' => $data, 'list_unit' => $list_unit]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['unit' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = KonfigurasiUnit::where('unit', '=', $request->unit)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->unit . " already exist!");
        }
        try {
            $konfigurasiunit= KonfigurasiUnit::find($id);;   
            $konfigurasiunit->unit 					= $request->unit; 
            $konfigurasiunit->debit_kiri 			= $request->debit_kiri; 
            $konfigurasiunit->debit_kanan 			= $request->debit_kanan; 
            $konfigurasiunit->koefisien_sayap_kiri 	= $request->koefisien_sayap_kiri; 
            $konfigurasiunit->koefisien_sayap_kanan = $request->koefisien_sayap_kanan; 
            $konfigurasiunit->minimum_spray_kiri 	= $request->minimum_spray_kiri; 
            $konfigurasiunit->minimum_spray_kanan 	= $request->minimum_spray_kanan; 
            $konfigurasiunit->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/konfigurasi_unit')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $konfigurasiunit = KonfigurasiUnit::find($id);
            $konfigurasiunit->delete();
            return redirect('master/konfigurasi_unit')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

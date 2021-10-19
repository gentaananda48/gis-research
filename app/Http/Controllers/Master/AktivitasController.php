<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Aktivitas;
use App\Model\GrupAktivitas;
use App\Model\AktivitasParameter;
use App\Model\Parameter;
use App\Center\GridCenter;
use App\Transformer\AktivitasTransformer;

class AktivitasController extends Controller {
    public function index() {
        return view('master.aktivitas.index');
    }

    public function get_list(){
        $query = Aktivitas::leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'aktivitas.grup_id')
        ->select(['aktivitas.*', 'ga.nama AS grup_nama']);
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new AktivitasTransformer()));
        exit;
    }

    public function create() {
        $res = GrupAktivitas::get(['id', 'nama']);
        foreach($res AS $v){
            $list_grup_aktivitas[$v->id] = $v->nama;
        }
        return view('master.aktivitas.create', [
            'list_grup_aktivitas' 	=> $list_grup_aktivitas
        ]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'nama' => 'required','grup_id' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Aktivitas::where('kode', '=', $request->kode)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $aktivitas= new Aktivitas;   
            $aktivitas->kode 	= $request->input('kode'); 
            $aktivitas->nama 	= $request->input('nama'); 
            $aktivitas->grup_id = $request->input('grup_id');
            $aktivitas->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/aktivitas')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = Aktivitas::find($id);
        $list_grup_aktivitas = [];
        $res = GrupAktivitas::get(['id', 'nama']);
        foreach($res AS $v){
            $list_grup_aktivitas[$v->id] = $v->nama;
        }
        return view('master.aktivitas.edit', [
            'data' => $data,
            'list_grup_aktivitas' 	=> $list_grup_aktivitas
        ]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Aktivitas::where('kode', '=', $request->kode)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $aktivitas= Aktivitas::find($id);   
            $aktivitas->kode 	= $request->input('kode'); 
            $aktivitas->nama 	= $request->input('nama'); 
            $aktivitas->grup_id = $request->input('grup_id');
            $aktivitas->save();

        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/aktivitas')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try {
            $aktivitas= Aktivitas::find($id);
            $aktivitas->delete();
            return redirect('master/aktivitas')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function parameter($id) {
        $data = Aktivitas::find($id);
        $list_parameter = AktivitasParameter::leftJoin('parameter', 'parameter.id', '=', 'aktivitas_parameter.parameter_id')
            ->where('aktivitas_id', $id)
            ->get(['aktivitas_parameter.*', 'parameter.nama AS parameter_nama']);
        return view('master.aktivitas.parameter', ['data' => $data, 'list_parameter' => $list_parameter]);
    }

    public function parameter_update(Request $request, $id) {
        try {
            foreach($request->id as $id){
                $ap = AktivitasParameter::find($id);
                $ap->standard   = $request->standard[$id];
                $ap->minimal    = $request->minimal[$id];
                $ap->maximal    = $request->maximal[$id];
                $ap->bobot      = $request->bobot[$id];
                $ap->save();
            }
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/aktivitas')->with('message', 'Saved successfully');
    }
    
}

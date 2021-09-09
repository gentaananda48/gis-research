<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Unit;
use App\Center\GridCenter;
use App\Transformer\UnitTransformer;

class UnitController extends Controller {
    public function index() {
        return view('master.unit.index');
    }

    public function getList(){
        $query = Unit::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new UnitTransformer()));
        exit;
    }

    public function create() {
        return view('master.unit.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Unit::where('kode', '=', $request->kode)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $unit= new Unit;   
            $unit->kode 	= $request->input('kode'); 
            $unit->nama 	= $request->input('nama'); 
            $unit->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/unit')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = Unit::find($id);
        return view('master.unit.edit', ['data' => $data]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $validated_fields = ['kode' => 'required', 'nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Unit::where('kode', '=', $request->kode)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->kode . " already exist!");
        }
        try {
            $unit= Unit::find($id);   
            $unit->kode 	= $request->input('kode'); 
            $unit->nama 	= $request->input('nama'); 
            $unit->save();

        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/unit')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try {
            $unit= Unit::find($id);
            $unit->delete();
            return redirect('master/unit')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

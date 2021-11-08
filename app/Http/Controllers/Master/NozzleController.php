<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Nozzle;
use App\Center\GridCenter;
use App\Transformer\NozzleTransformer;

class NozzleController extends Controller {
    public function index() {
        return view('master.nozzle.index');
    }

    public function get_list(){
        $query = Nozzle::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new NozzleTransformer()));
        exit;
    }

    public function create()
    {
        return view('master.nozzle.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = [
            'nama' => 'required', 'nama' => 'required'
        ];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = Nozzle::where('nama', '=', $request->nama)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $nozzle= new Nozzle;   
            $nozzle->nama        = $request->input('nama'); 
            $nozzle->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/nozzle')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = Nozzle::find($id);
        return view('master.nozzle.edit', ['data' => $data]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['nama' => 'required', 'nama' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Nozzle::where('nama', '=', $request->nama)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $nozzle= Nozzle::find($id);;   
            $nozzle->nama        = $request->input('nama'); 
            $nozzle->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/nozzle')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $nozzle = Nozzle::find($id);
            $nozzle->delete();
            return redirect('master/nozzle')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

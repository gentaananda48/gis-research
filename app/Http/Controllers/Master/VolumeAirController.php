<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\VolumeAir;
use App\Center\GridCenter;
use App\Transformer\VolumeAirTransformer;

class VolumeAirController extends Controller {
    public function index() {
        return view('master.volume_air.index');
    }

    public function get_list(){
        $query = VolumeAir::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new VolumeAirTransformer()));
        exit;
    }

    public function create()
    {
        return view('master.volume_air.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = [
            'volume' => 'required|numeric'
        ];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = VolumeAir::where('volume', '=', $request->volume)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->volume . " already exist!");
        }
        try {
            $volume_air= new VolumeAir;   
            $volume_air->volume        = $request->input('volume'); 
            $volume_air->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/volume_air')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = VolumeAir::find($id);
        return view('master.volume_air.edit', ['data' => $data]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = [
            'volume' => 'required|numeric'
        ];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = VolumeAir::where('volume', '=', $request->volume)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->volume . " already exist!");
        }
        try {
            $volume_air= VolumeAir::find($id);;   
            $volume_air->volume        = $request->input('volume'); 
            $volume_air->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/volume_air')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $volume_air = VolumeAir::find($id);
            $volume_air->delete();
            return redirect('master/volume_air')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

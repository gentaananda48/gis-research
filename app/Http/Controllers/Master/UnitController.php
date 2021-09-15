<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Unit;
use App\Center\GridCenter;
use App\Transformer\UnitTransformer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller {
    protected $base_url = 'https://api.lacak.io';
    protected $hash = '375f851d60cb30450125d5414c6b76c7';

    public function index() {
        try {
            $list_unit = Unit::get();
            $list_lacak_id = [];
            foreach($list_unit AS $v){
                $list_lacak_id[] = $v->lacak_id;
            }
            $trackers = '['.join(',',$list_lacak_id).']';
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/get_states', [
                'form_params' => [
                    'hash'      => $this->hash,
                    'trackers'  => $trackers
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->states AS $k=>$v) {
                $unit = Unit::where('lacak_id', $k)->first();
                if($unit!=null){
                    $unit->gps_updated = $v->gps->updated;
                    $unit->gps_signal_level = $v->gps->signal_level;
                    $unit->gps_location_lat = $v->gps->location->lat;
                    $unit->gps_location_lng = $v->gps->location->lng;
                    $unit->gps_heading = $v->gps->heading;
                    $unit->gps_speed = $v->gps->speed;
                    $unit->gps_alt = $v->gps->alt;
                    $unit->movement_status = $v->movement_status;
                    $unit->save();
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false, 
                'message'   => $e->getMessage(), 
                'data'      => null
            ]);
        }
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

            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/list', [
                'form_params' => [
                    'hash'      => $this->hash,
                    'labels'    => '["'.$unit->kode.'"]'
                ]
            ]);
            $body = json_decode($res->getBody());
            if(count($body->list)>0){
                foreach($body->list AS $v) {
                    if($v->label==$unit->kode){
                        $unit->lacak_id = $v->id;
                        $unit->save();
                    }
                }
            } else {
                return redirect()->back()->withErrors($unit->kode." not found in Lacak System");
            }
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

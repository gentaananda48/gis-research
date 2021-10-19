<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\ReportParameterBobot;
use App\Model\ReportParameterBobotDetail;
use App\Model\GrupAktivitas;
use App\Model\ReportParameter;
use App\Center\GridCenter;
use App\Transformer\ReportParameterBobotTransformer;

class ReportParameterBobotController extends Controller {
    public function index() {
        return view('master.report_parameter_bobot.index');
    }

    public function get_list(){
        $query = ReportParameterBobot::leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'report_parameter_bobot.grup_aktivitas_id')
            ->leftJoin('report_parameter AS rp', 'rp.id', '=', 'report_parameter_bobot.report_parameter_id')
            ->select(['report_parameter_bobot.*', 'ga.nama AS grup_aktivitas_nama', 'rp.nama AS report_parameter_nama']);
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportParameterBobotTransformer()));
        exit;
    }

    public function create()
    {
        $list_grup_aktivitas = [];
        $list_report_parameter = [];
        $res = GrupAktivitas::get(['id', 'nama']);
        foreach($res AS $v){
            $list_grup_aktivitas[$v->id] = $v->nama;
        }
        $res = ReportParameter::get(['id', 'nama']);
        foreach($res AS $v){
            $list_report_parameter[$v->id] = $v->nama;
        }
        return view('master.report_parameter_bobot.create', [
        	'list_grup_aktivitas' 	=> $list_grup_aktivitas,
        	'list_report_parameter'	=> $list_report_parameter
        ]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['grup_aktivitas_id' => 'required', 'report_parameter_id' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportParameterBobot::where('grup_aktivitas_id', '=', $request->grup_aktivitas_id)
        	->where('report_parameter_id', '=', $request->report_parameter_id)
        	->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors("Already exist!");
        }
        try {
            $report_parameter_bobot= new ReportParameterBobot;   
            $report_parameter_bobot->grup_aktivitas_id 		= $request->input('grup_aktivitas_id');  
            $report_parameter_bobot->report_parameter_id 	= $request->input('report_parameter_id'); 
            $report_parameter_bobot->bobot        			= $request->input('bobot'); 
            $report_parameter_bobot->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/report_parameter_bobot')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = ReportParameterBobot::find($id);
        $list_grup_aktivitas = [];
        $list_report_parameter = [];
        $res = GrupAktivitas::get(['id', 'nama']);
        foreach($res AS $v){
            $list_grup_aktivitas[$v->id] = $v->nama;
        }
        $res = ReportParameter::get(['id', 'nama']);
        foreach($res AS $v){
            $list_report_parameter[$v->id] = $v->nama;
        }
        return view('master.report_parameter_bobot.edit', [
        	'data' 					=> $data, 
        	'list_grup_aktivitas' 	=> $list_grup_aktivitas,
        	'list_report_parameter'	=> $list_report_parameter
        ]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $validated_fields = ['grup_aktivitas_id' => 'required', 'report_parameter_id' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportParameterBobot::where('grup_aktivitas_id', '=', $request->grup_aktivitas_id)
        	->where('report_parameter_id', '=', $request->report_parameter_id)
        	->where('id', '<>', $id)
        	->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors("Already exist!");
        }
        try {
            $report_parameter_bobot= ReportParameterBobot::find($id);;   
            $report_parameter_bobot->grup_aktivitas_id 		= $request->input('grup_aktivitas_id');  
            $report_parameter_bobot->report_parameter_id 	= $request->input('report_parameter_id'); 
            $report_parameter_bobot->bobot        			= $request->input('bobot'); 
            $report_parameter_bobot->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/report_parameter_bobot')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $report_parameter_bobot = ReportParameterBobot::find($id);
            $report_parameter_bobot->delete();
            ReportParameterBobotDetail::where('report_parameter_bobot_id', $id)->delete();
            return redirect('master/report_parameter_bobot')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

}

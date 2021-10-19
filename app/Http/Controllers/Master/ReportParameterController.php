<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\ReportParameter;
use App\Center\GridCenter;
use App\Transformer\ReportParameterTransformer;

class ReportParameterController extends Controller {
    public function index() {
        return view('master.report_parameter.index');
    }

    public function get_list() {
        $query = ReportParameter::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportParameterTransformer()));
        exit;
    }

    public function create() {
        return view('master.report_parameter.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportParameter::where('nama', '=', $request->nama)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $report_parameter= new ReportParameter;   
            $report_parameter->nama 		= $request->input('nama'); 
            $report_parameter->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/report_parameter')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = ReportParameter::find($id);
        return view('master.report_parameter.edit', ['data' => $data]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
       	$validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportParameter::where('nama', '=', $request->nama)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $report_parameter= ReportParameter::find($id);;   
            $report_parameter->nama 	= $request->input('nama'); 
            $report_parameter->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/report_parameter')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $report_parameter = ReportParameter::find($id);
            $report_parameter->delete();
            return redirect('master/report_parameter')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

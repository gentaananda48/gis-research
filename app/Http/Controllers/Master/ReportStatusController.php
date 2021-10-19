<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\ReportStatus;
use App\Center\GridCenter;
use App\Transformer\ReportStatusTransformer;

class ReportStatusController extends Controller {
    public function index() {
        return view('master.report_status.index');
    }

    public function get_list() {
        $query = ReportStatus::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportStatusTransformer()));
        exit;
    }

    public function create() {
        return view('master.report_status.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['status' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportStatus::where('status', '=', $request->status)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->status . " already exist!");
        }
        try {
            $report_status= new ReportStatus;   
            $report_status->status 		= $request->input('status'); 
            $report_status->range_1     = $request->input('range_1');
            $report_status->range_2 	= $request->input('range_2');
            $report_status->icon 		= $request->input('icon');
            $report_status->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/report_status')->with('message', 'Saved successfully');
    }
    public function show($id) {
        //
    }

    public function edit($id) {
        $data = ReportStatus::find($id);
        return view('master.report_status.edit', ['data' => $data]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
       	$validated_fields = ['status' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportStatus::where('status', '=', $request->status)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->status . " already exist!");
        }
        try {
            $report_status= ReportStatus::find($id);;   
            $report_status->status 		= $request->input('status'); 
            $report_status->range_1     = $request->input('range_1');
            $report_status->range_2 	= $request->input('range_2');
            $report_status->icon 		= $request->input('icon');
            $report_status->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/report_status')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $report_status = ReportStatus::find($id);
            $report_status->delete();
            return redirect('master/report_status')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

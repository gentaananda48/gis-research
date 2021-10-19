<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterStandardDetail;
use App\Model\ReportParameterBobot;
use App\Model\ReportParameterDefault;
use App\Model\ReportStatus;
use App\Model\Aktivitas;
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Center\GridCenter;
use App\Transformer\ReportParameterStandardTransformer;

class ReportParameterStandardController extends Controller {
    public function index() {
        return view('master.report_parameter_standard.index');
    }

    public function get_list(){
        $query = ReportParameterStandard::leftJoin('aktivitas AS a', 'a.id', '=', 'report_parameter_standard.aktivitas_id')
            ->leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'a.grup_id')
            ->leftJoin('nozzle AS n', 'n.id', '=', 'report_parameter_standard.nozzle_id')
            ->leftJoin('volume_air AS v', 'v.id', '=', 'report_parameter_standard.volume_id')
            ->select(['report_parameter_standard.*', 'a.nama AS aktivitas_nama', 'ga.nama AS grup_aktivitas_nama', 'n.nama AS nozzle_nama', 'v.volume']);
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new ReportParameterStandardTransformer()));
        exit;
    }

    public function create()
    {
        $list_aktivitas = [];
        $list_nozzle = [];
        $list_volume = [];
        $res = Aktivitas::leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'aktivitas.grup_id')
            ->get(['aktivitas.id', 'aktivitas.nama', 'ga.nama AS grup_nama']);
        foreach($res AS $v){
            $list_aktivitas[$v->id] = '['.$v->grup_nama.'] '.$v->nama;
        }
        $res = Nozzle::get(['id', 'nama']);
        foreach($res AS $v){
            $list_nozzle[$v->id] = $v->nama;
        }
        $res = VolumeAir::get(['id', 'volume']);
        foreach($res AS $v){
            $list_volume[$v->id] = $v->volume;
        }
        return view('master.report_parameter_standard.create', [
        	'list_aktivitas'    	=> $list_aktivitas,
            'list_nozzle'       	=> $list_nozzle,
            'list_volume'       	=> $list_volume
        ]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['aktivitas_id' => 'required', 'nozzle_id' => 'required', 'volume_id' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = ReportParameterStandard::where('aktivitas_id', '=', $request->aktivitas_id)
        	->where('nozzle_id', '=', $request->nozzle_id)
        	->where('volume_id', '=', $request->volume_id)
        	->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors("Already exist!");
        }
        try {
            $report_parameter_standard= new ReportParameterStandard;   
            $report_parameter_standard->aktivitas_id        = $request->input('aktivitas_id');  
            $report_parameter_standard->nozzle_id    		= $request->input('nozzle_id'); 
            $report_parameter_standard->volume_id        	= $request->input('volume_id'); 
            $report_parameter_standard->save();
            $aktivitas = Aktivitas::find($request->input('aktivitas_id'));
            $list_rpd = ReportParameterDefault::where('grup_aktivitas_id', $aktivitas->grup_id)
                ->orderBy('report_parameter_id', 'ASC')
                ->orderBy('urutan', 'ASC')
                ->get();
            foreach($list_rpd AS $v){
                $rpd = new ReportParameterStandardDetail;
                $rpd->report_parameter_standard_id   = $report_parameter_standard->id;
                $rpd->report_parameter_id   = $v->report_parameter_id;
                $rpd->urutan                = $v->urutan;
                $rpd->range_1               = 0;
                $rpd->range_2               = 0;
                $rpd->point                 = 0;
                $rpd->save();
            }
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/report_parameter_standard')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = ReportParameterStandard::find($id);
        $list_aktivitas = [];
        $list_nozzle = [];
        $list_volume = [];
        $res = Aktivitas::leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'aktivitas.grup_id')
            ->get(['aktivitas.id', 'aktivitas.nama', 'ga.nama AS grup_nama']);
        foreach($res AS $v){
            $list_aktivitas[$v->id] = '['.$v->grup_nama.'] '.$v->nama;
        }
        $res = Nozzle::get(['id', 'nama']);
        foreach($res AS $v){
            $list_nozzle[$v->id] = $v->nama;
        }
        $res = VolumeAir::get(['id', 'volume']);
        foreach($res AS $v){
            $list_volume[$v->id] = $v->volume;
        }
        return view('master.report_parameter_standard.edit', [
            'data'                  => $data, 
            'list_aktivitas'        => $list_aktivitas,
            'list_nozzle'           => $list_nozzle,
            'list_volume'           => $list_volume
        ]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['aktivitas_id' => 'required', 'nozzle_id' => 'required', 'volume_id' => 'required'];

        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = ReportParameterStandard::where('aktivitas_id', '=', $request->aktivitas_id)
            ->where('nozzle_id', '=', $request->nozzle_id)
            ->where('volume_id', '=', $request->volume_id)
        	->where('id', '<>', $id)
        	->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors("Already exist!");
        }
        try {
            $report_parameter_standard= ReportParameterStandard::find($id);;   
            $report_parameter_standard->aktivitas_id        = $request->input('aktivitas_id');  
            $report_parameter_standard->nozzle_id           = $request->input('nozzle_id'); 
            $report_parameter_standard->volume_id           = $request->input('volume_id');  
            $report_parameter_standard->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/report_parameter_standard')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $report_parameter_standard = ReportParameterStandard::find($id);
            $report_parameter_standard->delete();
            ReportParameterStandardDetail::where('report_parameter_standard_id', $id)->delete();
            return redirect('master/report_parameter_standard')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function detail($id) {
        $data = ReportParameterStandard::leftJoin('aktivitas AS a', 'a.id', '=', 'report_parameter_standard.aktivitas_id')
            ->leftJoin('grup_aktivitas AS ga', 'ga.id', '=', 'a.grup_id')
            ->leftJoin('nozzle AS n', 'n.id', '=', 'report_parameter_standard.nozzle_id')
            ->leftJoin('volume_air AS v', 'v.id', '=', 'report_parameter_standard.volume_id')
            ->where('report_parameter_standard.id', $id)
            ->first(['report_parameter_standard.*', 'a.nama AS aktivitas_nama', 'ga.id AS grup_aktivitas_id', 'ga.nama AS grup_aktivitas_nama', 'n.nama AS nozzle_nama', 'v.volume']);
        $list_detail = ReportParameterStandardDetail::leftJoin('report_parameter AS rp', 'rp.id', '=', 'report_parameter_standard_detail.report_parameter_id')
            ->where('report_parameter_standard_id', $id)
            ->orderBy('report_parameter_id', 'ASC')
            ->orderBy('urutan', 'ASC')
            ->get(['report_parameter_standard_detail.*', 'rp.nama AS report_parameter_nama']);
        $list_detail2 = [];
        $list_report_status = ReportStatus::orderBy('range_1', 'ASC')->get();
        foreach($list_detail as $v){
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $data->grup_aktivitas_id)
                ->where('report_parameter_id', $v->report_parameter_id)
                ->first();
            $v->bobot = $rpb->bobot;
            $v->nilai = $v->point * $rpb->bobot;
            $rpd = ReportParameterDefault::where('grup_aktivitas_id', $data->grup_aktivitas_id)
                ->where('report_parameter_id', $v->report_parameter_id)
                ->where('urutan', $v->urutan)
                ->first();
            $v->status = $rpd->note;
            $list_detail2[] = $v;
        }
        return view('master.report_parameter_standard.detail', ['data' => $data, 'list_detail' => $list_detail2]);
    }

    public function detail_update(Request $request, $id) {
        try {
            foreach($request->id as $id){
                $rpd = ReportParameterStandardDetail::find($id);
                $rpd->range_1    = $request->range_1[$id];
                $rpd->range_2    = $request->range_2[$id];
                $rpd->point      = $request->point[$id];
                $rpd->save();
            }
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/report_parameter_standard')->with('message', 'Saved successfully');
    }
}

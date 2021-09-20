<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\TindakLanjutPending;
use App\Center\GridCenter;
use App\Transformer\TindakLanjutPendingTransformer;

class TindakLanjutPendingController extends Controller {
    public function index() {
        return view('master.tindak_lanjut_pending.index');
    }

    public function getList(){
        $query = TindakLanjutPending::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new TindakLanjutPendingTransformer()));
        exit;
    }

    public function create() {
        return view('master.tindak_lanjut_pending.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = TindakLanjutPending::where('nama', '=', $request->nama)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $tindak_lanjut_pending= new TindakLanjutPending;   
            $tindak_lanjut_pending->nama 	= $request->input('nama'); 
            $tindak_lanjut_pending->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/tindak_lanjut_pending')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = TindakLanjutPending::find($id);
        return view('master.tindak_lanjut_pending.edit', ['data' => $data]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = TindakLanjutPending::where('nama', '=', $request->nama)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $tindak_lanjut_pending= TindakLanjutPending::find($id);  
            $tindak_lanjut_pending->nama 	= $request->input('nama'); 
            $tindak_lanjut_pending->save();

        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/tindak_lanjut_pending')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try {
            $tindak_lanjut_pending= TindakLanjutPending::find($id);
            $tindak_lanjut_pending->delete();
            return redirect('master/tindak_lanjut_pending')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

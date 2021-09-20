<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\AlasanPending;
use App\Center\GridCenter;
use App\Transformer\AlasanPendingTransformer;

class AlasanPendingController extends Controller {
    public function index() {
        return view('master.alasan_pending.index');
    }

    public function getList(){
        $query = AlasanPending::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new AlasanPendingTransformer()));
        exit;
    }

    public function create() {
        return view('master.alasan_pending.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        $validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = AlasanPending::where('nama', '=', $request->nama)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $alasan_pending= new AlasanPending;   
            $alasan_pending->nama 	= $request->input('nama'); 
            $alasan_pending->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('master/alasan_pending')->with('message', 'Saved successfully');
    }

    public function edit($id) {
        $data = AlasanPending::find($id);
        return view('master.alasan_pending.edit', ['data' => $data]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $validated_fields = ['nama' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()) {
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        $isUsed = AlasanPending::where('nama', '=', $request->nama)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->nama . " already exist!");
        }
        try {
            $alasan_pending= AlasanPending::find($id);   
            $alasan_pending->nama 	= $request->input('nama'); 
            $alasan_pending->save();

        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/alasan_pending')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try {
            $alasan_pending= AlasanPending::find($id);
            $alasan_pending->delete();
            return redirect('master/alasan_pending')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}

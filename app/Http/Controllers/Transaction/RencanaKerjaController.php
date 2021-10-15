<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Model\RencanaKerja;
use App\Center\GridCenter;
use App\Transformer\RencanaKerjaTransformer;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RencanaKerjaImport;

class RencanaKerjaController extends Controller {
    public function index() {
        return view('transaction.rencana_kerja.index');
    }

    public function getList(){
        $query = RencanaKerja::orderBy('tgl', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new RencanaKerjaTransformer()));
        exit;
    }

    public function show($id) {
        $data = RencanaKerja::find($id);
        return view('transaction.rencana_kerja.edit', ['data' => $data]);
    }

    public function import_rencana_kerja(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);

        $import = Excel::import(new RencanaKerjaImport,request()->file('file'));
        if($import) {
            //redirect
            return redirect()->route('transaction.rencana_kerja')->with(['success' => 'Data Berhasil Diimport!']);
        } else {
            //redirect
            return redirect()->route('transaction.rencana_kerja')->with(['error' => 'Data Gagal Diimport!']);
        }
    }
}

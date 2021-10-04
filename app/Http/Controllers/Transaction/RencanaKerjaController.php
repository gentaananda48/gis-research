<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\RencanaKerja;
use App\Center\GridCenter;
use App\Transformer\RencanaKerjaTransformer;

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

}

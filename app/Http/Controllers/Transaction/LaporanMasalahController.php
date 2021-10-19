<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\LaporanMasalah;
use App\Center\GridCenter;
use App\Transformer\LaporanMasalahTransformer;

class LaporanMasalahController extends Controller {
    public function index() {
        return view('transaction.laporan_masalah.index');
    }

    public function get_list(){
        $query = LaporanMasalah::orderBy('tanggal', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new LaporanMasalahTransformer()));
        exit;
    }

    public function show($id) {
        $data = LaporanMasalah::find($id);
        return view('transaction.laporan_masalah.show', ['data' => $data]);
    }

}

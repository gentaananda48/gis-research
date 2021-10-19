<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Pemeliharaan;
use App\Center\GridCenter;
use App\Transformer\PemeliharaanTransformer;

class PemeliharaanController extends Controller {
    public function index() {
        return view('transaction.pemeliharaan.index');
    }

    public function get_list(){
        $query = Pemeliharaan::orderBy('tanggal', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new PemeliharaanTransformer()));
        exit;
    }

    public function show($id) {
        $data = Pemeliharaan::find($id);
        return view('transaction.pemeliharaan.show', ['data' => $data]);
    }

}

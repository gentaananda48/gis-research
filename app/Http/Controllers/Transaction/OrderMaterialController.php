<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\OrderMaterial;
use App\Center\GridCenter;
use App\Transformer\OrderMaterialTransformer;

class OrderMaterialController extends Controller {
    public function index() {
        return view('transaction.order_material.index');
    }

    public function getList(){
        $query = OrderMaterial::orderBy('tanggal', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new OrderMaterialTransformer()));
        exit;
    }

    public function show($id) {
        $data = OrderMaterial::find($id);
        return view('transaction.order_material.show', ['data' => $data]);
    }

}

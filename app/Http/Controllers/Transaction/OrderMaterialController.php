<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\OrderMaterial;
use App\Model\OrderMaterialBahan;
use App\Model\OrderMaterialLog;
use App\Center\GridCenter;
use App\Transformer\OrderMaterialTransformer;

class OrderMaterialController extends Controller {
    public function index() {
        return view('transaction.order_material.index');
    }

    public function get_list(){
        $query = OrderMaterial::orderBy('tanggal', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new OrderMaterialTransformer()));
        exit;
    }

    public function show($id) {
        $data = OrderMaterial::find($id);
        $bahan = OrderMaterialBahan::where('order_material_id', $id)->get();
        $data->bahan = $bahan;
        return view('transaction.order_material.show', ['data' => $data]);
    }

}

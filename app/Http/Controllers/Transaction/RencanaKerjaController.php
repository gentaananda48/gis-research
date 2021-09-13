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
        $query = RencanaKerja::select(['rencana_kerja.*', 's.nama AS shift_nama', 'l.kode AS lokasi_kode', 'l.nama AS lokasi_nama', 'a.kode AS aktivitas_kode', 'a.nama AS aktivitas_nama', 'u.kode AS unit_kode', 'u.nama AS unit_nama', 'o.name AS operator_nama', 'k.name AS kasie_nama', 's2.nama AS status_nama', 's2.color AS status_color'])
            ->leftJoin('shift AS s', 's.id', '=', 'rencana_kerja.shift_id')
            ->leftJoin('lokasi AS l', 'l.id', '=', 'rencana_kerja.lokasi_id')
            ->leftJoin('aktivitas AS a', 'a.id', '=', 'rencana_kerja.aktivitas_id')
            ->leftJoin('unit as u', 'u.id', '=', 'rencana_kerja.unit_id')
            ->leftJoin('users AS o', 'o.id', '=', 'rencana_kerja.operator_id')
            ->leftJoin('users AS k', 'k.id', '=', 'rencana_kerja.kasie_id')
            ->leftJoin('status AS s2', 's2.id', '=', 'rencana_kerja.status_id')
            ->orderBy('tgl', 'DESC');
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new RencanaKerjaTransformer()));
        exit;
    }

    public function show($id) {
        $data = RencanaKerja::find($id);
        return view('transaction.rencana_kerja.edit', ['data' => $data]);
    }

}

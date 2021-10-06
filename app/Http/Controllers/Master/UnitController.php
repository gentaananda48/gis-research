<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Unit;
use App\Center\GridCenter;
use App\Transformer\UnitTransformer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller {
    protected $base_url = 'https://api.lacak.io';
    protected $hash = '3eb8df6bd3c3e09cf1c8f2051f44b56d';

    public function index() {
        return view('master.unit.index');
    }

    public function getList(){
        $query = Unit::select();
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new UnitTransformer()));
        exit;
    }

    public function sync(){
        try {
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/list', [
                'form_params' => [
                    'hash'      => $this->hash,
                    'labels'    => '["BSC"]'
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->list AS $v) {
                $unit = Unit::where('id', $v->id)->first();
                if($unit==null){
                    $unit = new Unit;
                    $unit->id = $v->id;
                }
                $unit->label = $v->label;
                $unit->group_id = $v->group_id;
                $unit->source_id = $v->source->id;
                $unit->source_device_id = $v->source->device_id;
                $unit->source_model = $v->source->model;
                $unit->source_phone = $v->source->phone;
                $unit->save();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/unit')->with('message', 'Synced successfully');
    }

}

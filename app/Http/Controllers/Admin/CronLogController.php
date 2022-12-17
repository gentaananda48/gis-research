<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use App\Model\CronLog;
use App\Center\GridCenter;
use App\Transformer\CronLogTransformer;
use Intervention\Image\ImageManager;
use Auth;

class CronLogController extends Controller {
    public function index(Request $request) {
        if(empty($request->tgl)){
            $tgl = date('m/01/Y').' - '.date('m/t/Y');
            return redirect()->route('admin.cron_log', ['tgl' => $tgl]);
        }
        return view('admin.cron_log.index', [
        	'tgl' => $request->tgl
        ]);
    }

    public function get_list(Request $request){
        $query = CronLog::select();
        if(!empty($_GET['name'])){
            $query->where('users.name', 'like', '%'.$request->name.'%');
        }
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d 00:00:00', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d 23:59:59', strtotime($tgl[1]));
            $query->whereBetween('created_at', [$tgl_1, $tgl_2]);
        }
        $data = new GridCenter($query, $_GET);
    
        echo json_encode($data->render(new CronLogTransformer()));
        exit;
    }

    protected function guard(){
        return Auth::guard('web');
    }
}

<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Model\Unit;
use App\Model\Lacak;
use App\Model\PG;
use App\Model\KoordinatLokasi;
use App\Model\SystemConfiguration;
use App\Center\GridCenter;
use App\Transformer\UnitTransformer;
use GuzzleHttp\Client;
use App\Helper\GeofenceHelper;

class UnitController extends Controller {
    public function index() {
        return view('master.unit.index');
    }

    public function get_list(){
        $user = $this->guard()->user();
        $query = Unit::select()->whereIn('pg', explode(',', $user->area));
        $data = new GridCenter($query, $_GET);
        echo json_encode($data->render(new UnitTransformer()));
        exit;
    }

    public function sync(){
        $base_url = SystemConfiguration::where('code', 'LACAK_API_URL')->first(['value'])->value;
        $hash = SystemConfiguration::where('code', 'LACAK_API_HASH')->first(['value'])->value;
        DB::beginTransaction();
        try {
            $client = new Client();
            $res = $client->request('POST', $base_url.'/tracker/list', [
                'form_params' => [
                    'hash'      => $hash,
                    'labels'    => '["BSC","BDF","BDM"]'
                ]
            ]);
            $body = json_decode($res->getBody());
            DB::table('unit')->delete();
            foreach($body->list AS $v) {
                $unit = new Unit;
                $unit->id               = $v->id;
                $unit->label            = $v->label;
                $unit->group_id         = $v->group_id;
                $unit->source_id        = $v->source->id;
                $unit->source_device_id = $v->source->device_id;
                $unit->source_model     = $v->source->model;
                $unit->source_phone     = $v->source->phone;
                $pg = PG::find($unit->group_id);
                if($pg!=null){
                    $unit->pg = $pg->nama;
                }
                $unit->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/unit')->with('message', 'Synced successfully');
    }

    public function track(Request $request, $id) {
        $unit = Unit::find($id);
        // $lacak = Lacak::where('ident', $unit->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first();
        // $unit->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        // $unit->position_longitude       = $lacak != null ? $lacak->position_longitude : 0;
        // $unit->movement_status          = $lacak != null ? $lacak->movement_status : 0;
        // $unit->movement_status_desc     = !empty($unit->movement_status) ? 'moving': 'stopped';
        // $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        // $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        // $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        // $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        // $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_1) ? 'On' : 'Off';
        // $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_2) ? 'On' : 'Off';
        
        // $geofenceHelper = new GeofenceHelper;
        // $list_polygon = $geofenceHelper->createListPolygon();
        // $unit->lokasi = $geofenceHelper->checkLocation($list_polygon, $unit->position_latitude, $unit->position_longitude);
        //$unit->lokasi = !empty($unit->lokasi) ? substr($unit->lokasi,0,strlen($unit->lokasi)-2) : '';

        return view('master.unit.track', [
            'unit'  => $unit
        ]);
    }

    public function track_json(Request $request, $id) {
        $unit = Unit::find($id);
        $lacak = Lacak::where('ident', $unit->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first();
        $unit->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        $unit->position_longitude       = $lacak != null ? $lacak->position_longitude : 0;
        $unit->movement_status          = $lacak != null ? $lacak->movement_status : 0;
        $unit->movement_status_desc     = !empty($unit->movement_status) ? 'moving': 'stopped';
        $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_1) ? 'On' : 'Off';
        $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_2) ? 'On' : 'Off';

        // $geofenceHelper = new GeofenceHelper;
        // $list_polygon = $geofenceHelper->createListPolygon();
        // $unit->lokasi = $geofenceHelper->checkLocation($list_polygon, $unit->position_latitude, $unit->position_longitude);
        $unit->lokasi = !empty($unit->lokasi) ? substr($unit->lokasi,0,strlen($unit->lokasi)-2) : '';

        echo json_encode($unit);
        exit;
    }

    public function lokasi(Request $request) {
        $coordinate = explode(',', $request->coordinate);
        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $lokasi = $geofenceHelper->checkLocation($list_polygon, trim($coordinate[0]), trim($coordinate[1]));
        $lokasi = !empty($lokasi) ? substr($lokasi,0,strlen($lokasi)-2) : '';
        echo json_encode(['lokasi' => $lokasi]);
        exit;
    }

    public function playback(Request $request, $id) {
        $tgl = !empty($request->tgl) ? $request->tgl : date('Y-m-d');
        $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '00:00:00';
        $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : '23:59:00';
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($id);
        $list_interval = [];
        for($i=1; $i<=10; $i++){
            $list_interval[$i*100] = ($i/10).' Detik';
        }
        $list = KoordinatLokasi::orderBy('lokasi', 'ASC')
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $geofenceHelper = new GeofenceHelper;
        $tgl_jam_mulai = $tgl.' '.$jam_mulai;
        $tgl_jam_selesai = $tgl.' '.$jam_selesai;
        $durasi = strtotime($tgl_jam_selesai) - strtotime($tgl_jam_mulai) + 1;
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($tgl_jam_mulai))
            ->where('timestamp', '<=', strtotime($tgl_jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'din_1', 'din_2', 'din_3', 'timestamp']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($tgl_jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return view('master.unit.playback', [
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'tgl'           => $tgl,
            'jam_mulai'     => $jam_mulai,
            'jam_selesai'   => $jam_selesai,
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
    }

    protected function guard(){
        return Auth::guard('web');
    }

}

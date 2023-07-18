<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Model\Unit;
use App\Model\Lacak;
use App\Model\Lacak2;
use App\Model\PG;
use App\Model\KoordinatLokasi;
use App\Model\SystemConfiguration;
use App\Center\GridCenter;
use App\Transformer\UnitTransformer;
use GuzzleHttp\Client;
use App\Helper\GeofenceHelper;
use Illuminate\Support\Facades\Redis;

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
            //DB::table('unit')->delete();
            foreach($body->list AS $v) {
                $unit = Unit::where('label', $v->label)->first();
                if($unit==null) {
                    $unit           = new Unit;
                    $unit->label    = $v->label;
                }
                $unit->id               = $v->id;
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
                $table_name = "lacak_".$unit->source_device_id;
                if (!Schema::hasTable($table_name)) {
                    Schema::create($table_name, function (Blueprint $table) {
                        $table->bigIncrements('id');
                        $table->double('latitude')->nullable();
                        $table->double('longitude')->nullable();
                        //$table->double('speed')->nullable();
                        $table->decimal('speed', 18, 2)->nullable();
                        $table->double('altitude')->nullable();
                        $table->double('arm_height_left')->nullable();
                        $table->double('arm_height_right')->nullable();
                        $table->double('temperature_left')->nullable();
                        $table->double('temperature_right')->nullable();
                        $table->tinyInteger('pump_switch_main')->nullable();
                        $table->tinyInteger('pump_switch_left')->nullable();
                        $table->tinyInteger('pump_switch_right')->nullable();
                        $table->double('flow_meter_left')->nullable();
                        $table->double('flow_meter_right')->nullable();
                        $table->double('tank_level')->nullable();
                        $table->double('oil')->nullable();
                        $table->double('gas')->nullable();
                        $table->double('homogenity')->nullable();
                        $table->double('bearing')->nullable();
                        $table->string('microcontroller_id', 30)->nullable();
                        $table->double('utc_timestamp')->nullable();
                        $table->double('device_timestamp')->unique()->nullable();
                        $table->dateTime('created_at')->nullable();
                        $table->string('box_id', 20)->nullable();
                        $table->string('unit_label', 30)->nullable();
                        $table->tinyInteger('processed')->nullable();
                        $table->date('report_date')->nullable();
                        $table->index('processed');
                        $table->index('report_date');
                    });
                }
                $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($unit->label)));
                if (!Schema::hasTable($table_name)) {
                    Schema::create($table_name, function (Blueprint $table) {
                        $table->bigIncrements('id');
                        $table->double('latitude')->nullable();
                        $table->double('longitude')->nullable();
                        //$table->double('speed')->nullable();
                        $table->decimal('speed', 18, 2)->nullable();
                        $table->double('altitude')->nullable();
                        $table->double('arm_height_left')->nullable();
                        $table->double('arm_height_right')->nullable();
                        $table->double('temperature_left')->nullable();
                        $table->double('temperature_right')->nullable();
                        $table->tinyInteger('pump_switch_main')->nullable();
                        $table->tinyInteger('pump_switch_left')->nullable();
                        $table->tinyInteger('pump_switch_right')->nullable();
                        $table->double('flow_meter_left')->nullable();
                        $table->double('flow_meter_right')->nullable();
                        $table->double('tank_level')->nullable();
                        $table->double('oil')->nullable();
                        $table->double('gas')->nullable();
                        $table->double('homogenity')->nullable();
                        $table->double('bearing')->nullable();
                        $table->string('microcontroller_id', 30)->nullable();
                        $table->double('utc_timestamp')->nullable();
                        $table->double('device_timestamp')->unique()->nullable();
                        $table->dateTime('created_at')->nullable();
                        $table->string('box_id', 20)->nullable();
                        $table->string('unit_label', 30)->nullable();
                        $table->string('source_device_id', 20)->nullable();
                        $table->string('lokasi_kode', 10)->nullable();
                        $table->tinyInteger('processed')->nullable();
                        $table->date('report_date')->nullable();
                        $table->index('processed');
                        $table->index('report_date');
                    });
                }
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
        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT2';
        $cached = Redis::get($cache_key);
        $list_lokasi = [];
        if(isset($cached)) {
            $list_lokasi = $cached;
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            foreach($list_koordinat_lokasi as $v){
                $idx = $v->lokasi.'_'.$v->bagian;
                if(array_key_exists($idx, $list_lokasi)){
                    $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
                } else {
                    $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
                }
            }
            $list_lokasi = array_values($list_lokasi);
            $list_lokasi = json_encode($list_lokasi);
            Redis::set($cache_key, $list_lokasi);
        }
        return view('master.unit.track', [
            'unit'          => $unit,
            'list_lokasi'   => $list_lokasi
        ]);
    }

    public function track_json(Request $request, $id) {
        $unit = Unit::find($id);
        $lacak = Lacak2::where('ident', $unit->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first(['position_latitude','position_longitude','movement_status','gsm_signal_level','position_altitude','position_direction','position_speed','din_2','din_3']);
        $unit->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        $unit->position_longitude       = $lacak != null ? $lacak->position_longitude : 0;
        $unit->movement_status          = $lacak != null ? $lacak->movement_status : 0;
        $unit->movement_status_desc     = !empty($unit->movement_status) ? 'moving': 'stopped';
        $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_2) ? 'On' : 'Off';
        $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_3) ? 'On' : 'Off';

        $geofenceHelper = new GeofenceHelper;
        //$list_polygon = $geofenceHelper->createListPolygon();
        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT';
        $cached = Redis::get($cache_key);
        $list_koordinat_lokasi = [];
        if(isset($cached)) {
            $list_koordinat_lokasi = json_decode($cached, FALSE);
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            Redis::set($cache_key, json_encode($list_koordinat_lokasi));
        }
        $list_polygon = [];
        foreach($list_koordinat_lokasi as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        
        $unit->lokasi = $geofenceHelper->checkLocation($list_polygon, $unit->position_latitude, $unit->position_longitude);
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
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit','2048M');
        set_time_limit(0);
        $tgl = !empty($request->tgl) ? $request->tgl : date('Y-m-d');
        $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '00:00:00';
        $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : date('H:i:s');
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($id);
        $list_interval = [];
        for($i=1; $i<=10; $i++){
            $list_interval[$i*100] = ($i/10).' Detik';
        }
        $cache_key = env('APP_CODE').':LOKASI:LIST_KOORDINAT';
        $cached = Redis::get($cache_key);
        $list_koordinat_lokasi = [];
        if(isset($cached)) {
            $list_koordinat_lokasi = json_decode($cached, FALSE);
        } else {
            $list_koordinat_lokasi = KoordinatLokasi::orderBy('lokasi', 'ASC')
                ->orderBy('bagian', 'ASC')
                ->orderBy('posnr', 'ASC')
                ->get();
            Redis::set($cache_key, json_encode($list_koordinat_lokasi));
        }
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list_koordinat_lokasi as $v){
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
        $cache_key = env('APP_CODE').':UNIT:PLAYBACK_'.$unit->source_device_id.'_'.$tgl;
        if($tgl >= date('Y-m-d')) {
            $redis_scan_result = Redis::scan(0, 'match', $cache_key.'*');
            $cache_key .= '_'.$jam_selesai;
            if(count($redis_scan_result[1])>0){
                rsort($redis_scan_result[1]);
                $last_key = $redis_scan_result[1][0];
                if($cache_key<$last_key){
                    $cache_key = $last_key;
                }
                foreach($redis_scan_result[1] as $key){
                    if($key!=$cache_key){
                        Redis::del($key);
                    }
                }
            }
        }
        $cached = Redis::get($cache_key);
        $list_lacak = [];
        if(isset($cached)) {
            $list_lacak = json_decode($cached, FALSE);
        } else {
            $timestamp_1 = $tgl >= date('Y-m-d') ? strtotime($tgl_jam_mulai) : strtotime($tgl.' 00:00:00');
            $timestamp_2 = $tgl >= date('Y-m-d') ? strtotime($tgl_jam_selesai) : strtotime($tgl.' 23:59:59');
            $list_lacak = Lacak2::where('ident', $unit->source_device_id)
                ->where('timestamp', '>=', $timestamp_1)
                ->where('timestamp', '<=', $timestamp_2)
                ->orderBy('timestamp', 'ASC')
                ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'din_2 AS pump_switch_right', 'din_3 AS pump_switch_left', 'din_4 AS pump_switch_main', 'payload_text', 'timestamp']);
            Redis::set($cache_key, json_encode($list_lacak), 'EX', 2592000);
        }
        $list_lacak2 = [];
        foreach($list_lacak as $v){
            if(strtotime($tgl_jam_mulai) <= doubleval($v->timestamp) && doubleval($v->timestamp) <= strtotime($tgl_jam_selesai)) {
                $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
                $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
                $v->progress_time = doubleval($v->timestamp) - strtotime($tgl_jam_mulai);
                $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
                $v->timestamp_2 = date('H:i:s', $v->timestamp);
                $list_lacak2[] = $v;
            }
        }
        return view('master.unit.playback', [
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak2),
            'list_lokasi'   => json_encode($list_lokasi),
            'tgl'           => $tgl,
            'jam_mulai'     => $jam_mulai,
            'jam_selesai'   => $jam_selesai,
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
        ini_set('memory_limit', $oldLimit);
    }

    public function playback2(Request $request, $id) {
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $tgl = !empty($request->tgl) ? $request->tgl : date('Y-m-d');
        $jam_mulai = !empty($request->jam_mulai) ? $request->jam_mulai : '00:00:00';
        $jam_selesai = !empty($request->jam_selesai) ? $request->jam_selesai : date('H:i:s');
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
        $lacak = Lacak2::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($tgl_jam_mulai))
            ->where('timestamp', '<=', strtotime($tgl_jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'din_2 AS pump_switch_right', 'din_3 AS pump_switch_left', 'din_4 AS pump_switch_main', 'payload_text', 'timestamp']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($tgl_jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        ini_set('memory_limit', $oldLimit);
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

    public function edit($id) {
        $data = Unit::find($id);
        return view('master.unit.edit', ['data' => $data]);

    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = ['box_id' => 'required'];
        $valid = Validator::make($post,$validated_fields);
        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }
        try {
            $unit = Unit::find($id);
            $unit->box_id   = $request->box_id; 
            $unit->save();
        } catch(Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
        return redirect('master/unit')->with('message', 'Saved successfully');
    }

    protected function guard(){
        return Auth::guard('web');
    }

}

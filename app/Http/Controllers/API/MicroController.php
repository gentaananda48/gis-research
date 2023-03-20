<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MicroController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function upload(Request $request){
        // \Log::info($request->all());
        if ($request->file('file_attachment')) {
             
            $path = public_path('upload/database');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $file = $request->file('file_attachment');
            $filename = time() . '.' . $request->file('file_attachment')->getClientOriginalExtension();
            $file->move($path, $filename);

            $pathname = public_path('upload/database/').$filename;
            \Config::set('database.connections.sqlite.database', $pathname);
            $sqliteDbConnection = \DB::connection('sqlite');
            $getData = $sqliteDbConnection->table("microcontroller_data_v5")->where('posted','N')->get();
            // \Log::info($getData);
            if ($getData) {
                $table_name = "microcontroller_data_".time();
                // dipakai jika membuat temporary table
                // Schema::create($table_name, function (Blueprint $table) {
                //     $table->increments('id');
                //     $table->double('utc_timestamp')->nullable();
                //     $table->string('microcontroller_id')->nullable();
                //     $table->string('source_device_id')->nullable();
                //     $table->double('latitude')->nullable();
                //     $table->double('longitude')->nullable();
                //     $table->double('speed')->nullable();
                //     $table->double('altitude')->nullable();
                //     $table->double('arm_height_left')->nullable();
                //     $table->double('arm_height_right')->nullable();
                //     $table->double('temperature_left')->nullable();
                //     $table->double('temperature_right')->nullable();
                //     $table->double('pump_switch_left')->nullable();
                //     $table->double('pump_switch_right')->nullable();
                //     $table->double('pump_switch_main')->nullable();
                //     $table->double('flow_meter_left')->nullable();
                //     $table->double('flow_meter_right')->nullable();
                //     $table->double('tank_level')->nullable();
                //     $table->double('oil')->nullable();
                //     $table->double('gas')->nullable();
                //     $table->double('homogenity')->nullable();
                //     $table->double('bearing')->nullable();
                //     $table->double('box_id')->nullable();
                //     $table->string('unit_label')->nullable();
                //     $table->integer('login_uid')->nullable();
                //     $table->string('posted')->nullable();
                //     $table->timestamps();
                    
                // });

                foreach ($getData as $data) {
                    if ($data->source_device_id != "860264058610701") {
                       continue;
                    }

                    $temp['utc_timestamp'] = $data->utc_timestamp ? $data->utc_timestamp:null;
                    $temp['microcontroller_id'] = $data->microcontroller_id ? $data->microcontroller_id:null;
                    $temp['latitude'] = $data->latitude ? $data->latitude:null;
                    $temp['longitude'] = $data->longitude ? $data->longitude:null;
                    $temp['speed'] = $data->speed ? $data->speed:null;
                    $temp['altitude'] = $data->altitude ? $data->altitude:null;
                    $temp['arm_height_left'] = $data->arm_height_left ? $data->arm_height_left:null;
                    $temp['arm_height_right'] = $data->arm_height_right ? $data->arm_height_right:null;
                    $temp['temperature_left'] = $data->temperature_left ? $data->temperature_left:null;
                    $temp['temperature_right'] = $data->temperature_right ? $data->temperature_right:null;
                    $temp['pump_switch_left'] = $data->pump_switch_left ? $data->pump_switch_left:null;
                    $temp['pump_switch_right'] = $data->pump_switch_right ? $data->pump_switch_right:null;
                    $temp['pump_switch_main'] = $data->pump_switch_main ? $data->pump_switch_main:null;
                    $temp['flow_meter_left'] = $data->flow_meter_left ? $data->flow_meter_left:null;
                    $temp['flow_meter_right'] = $data->flow_meter_right ? $data->flow_meter_right:null;
                    $temp['tank_level'] = $data->tank_level ? $data->tank_level:null;
                    $temp['oil'] = $data->oil ? $data->oil:null;
                    $temp['gas'] = $data->gas ? $data->gas:null;
                    $temp['homogenity'] = $data->homogenity ? $data->homogenity:null;
                    $temp['bearing'] = $data->bearing ? $data->bearing:null;
                    $temp['box_id'] = $data->box_id ? $data->box_id:null;
                    $temp['unit_label'] = $data->unit_label ? $data->unit_label:null;
                    $temp['created_at'] = date('Y-m-d H:i:s');

                    // \DB::table("lacak_".$data->source_device_id)->insert($temp);
                    \DB::table("lacak_860264058610701")->insert($temp);
                    
                }
            }
            
            unlink($pathname);
            return response()->json([
                "status" => true,
                "message" => "File successfully uploaded",
                "data" => array()
            ]);
  
        }

        return response()->json([
            'status'    => false, 
            'message'   => 'gagal simpan data', 
            'data'      => array()
        ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
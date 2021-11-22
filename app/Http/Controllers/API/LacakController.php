<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use App\Model\Lacak;

class LacakController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function create(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	    	$post = $request->all();
	    	$lacak = new Lacak();
	    	$lacak->ain_1  = isset($post["ain.1"]) ? $post["ain.1"] : null;
			$lacak->ain_2  = isset($post["ain.2"]) ? $post["ain.2"] : null;
			$lacak->battery_charging_status  = isset($post["battery.charging.status"]) ? $post["battery.charging.status"] : null;
			$lacak->battery_current  = isset($post["battery.current"]) ? $post["battery.current"] : null;
			$lacak->battery_temperature  = isset($post["battery.temperature"]) ? $post["battery.temperature"] : null;
			$lacak->battery_voltage  = isset($post["battery.voltage"]) ? $post["battery.voltage"] : null;
			$lacak->button_pressed_status  = isset($post["button.pressed.status"]) ? $post["button.pressed.status"] : null;
			$lacak->cable_connected_status  = isset($post["cable.connected.status"]) ? $post["cable.connected.status"] : null;
			$lacak->can_battery_voltage  = isset($post["can.battery.voltage"]) ? $post["can.battery.voltage"] : null;
			$lacak->can_car_closed_remote_status  = isset($post["can.car.closed.remote.status"]) ? $post["can.car.closed.remote.status"] : null;
			$lacak->can_car_closed_status  = isset($post["can.car.closed.status"]) ? $post["can.car.closed.status"] : null;
			$lacak->can_connection_state_1  = isset($post["can.connection.state.1"]) ? $post["can.connection.state.1"] : null;
			$lacak->can_connection_state_2  = isset($post["can.connection.state.2"]) ? $post["can.connection.state.2"] : null;
			$lacak->can_connection_state_3  = isset($post["can.connection.state.3"]) ? $post["can.connection.state.3"] : null;
			$lacak->can_driver_door_status  = isset($post["can.driver.door.status"]) ? $post["can.driver.door.status"] : null;
			$lacak->can_dynamic_ignition_status  = isset($post["can.dynamic.ignition.status"]) ? $post["can.dynamic.ignition.status"] : null;
			$lacak->can_engine_ignition_status  = isset($post["can.engine.ignition.status"]) ? $post["can.engine.ignition.status"] : null;
			$lacak->can_engine_load_level  = isset($post["can.engine.load.level"]) ? $post["can.engine.load.level"] : null;
			$lacak->can_engine_motorhours  = isset($post["can.engine.motorhours"]) ? $post["can.engine.motorhours"] : null;
			$lacak->can_engine_rpm  = isset($post["can.engine.rpm"]) ? $post["can.engine.rpm"] : null;
			$lacak->can_engine_temperature  = isset($post["can.engine.temperature"]) ? $post["can.engine.temperature"] : null;
			$lacak->can_engine_working_status  = isset($post["can.engine.working.status"]) ? $post["can.engine.working.status"] : null;
			$lacak->can_fuel_consumed  = isset($post["can.fuel.consumed"]) ? $post["can.fuel.consumed"] : null;
			$lacak->can_fuel_level  = isset($post["can.fuel.level"]) ? $post["can.fuel.level"] : null;
			$lacak->can_fuel_volume  = isset($post["can.fuel.volume"]) ? $post["can.fuel.volume"] : null;
			$lacak->can_handbrake_status  = isset($post["can.handbrake.status"]) ? $post["can.handbrake.status"] : null;
			$lacak->can_hood_status  = isset($post["can.hood.status"]) ? $post["can.hood.status"] : null;
			$lacak->can_ignition_key_status  = isset($post["can.ignition.key.status"]) ? $post["can.ignition.key.status"] : null;
			$lacak->can_lvc_module_control_bitmask  = isset($post["can.lvc.module.control.bitmask"]) ? $post["can.lvc.module.control.bitmask"] : null;
			$lacak->can_module_id  = isset($post["can.module.id"]) ? $post["can.module.id"] : null;
			$lacak->can_module_sleep_mode  = isset($post["can.module.sleep.mode"]) ? $post["can.module.sleep.mode"] : null;
			$lacak->can_parking_status  = isset($post["can.parking.status"]) ? $post["can.parking.status"] : null;
			$lacak->can_passenger_door_status  = isset($post["can.passenger.door.status"]) ? $post["can.passenger.door.status"] : null;
			$lacak->can_pedal_brake_status  = isset($post["can.pedal.brake.status"]) ? $post["can.pedal.brake.status"] : null;
			$lacak->can_program_id  = isset($post["can.program.id"]) ? $post["can.program.id"] : null;
			$lacak->can_rear_left_door_status  = isset($post["can.rear.left.door.status"]) ? $post["can.rear.left.door.status"] : null;
			$lacak->can_rear_right_door_status  = isset($post["can.rear.right.door.status"]) ? $post["can.rear.right.door.status"] : null;
			$lacak->can_reverse_gear_status  = isset($post["can.reverse.gear.status"]) ? $post["can.reverse.gear.status"] : null;
			$lacak->can_throttle_pedal_level  = isset($post["can.throttle.pedal.level"]) ? $post["can.throttle.pedal.level"] : null;
			$lacak->can_tracker_counted_mileage  = isset($post["can.tracker.counted.mileage"]) ? $post["can.tracker.counted.mileage"] : null;
			$lacak->can_trunk_status  = isset($post["can.trunk.status"]) ? $post["can.trunk.status"] : null;
			$lacak->can_vehicle_battery_level  = isset($post["can.vehicle.battery.level"]) ? $post["can.vehicle.battery.level"] : null;
			$lacak->can_vehicle_mileage  = isset($post["can.vehicle.mileage"]) ? $post["can.vehicle.mileage"] : null;
			$lacak->can_vehicle_speed  = isset($post["can.vehicle.speed"]) ? $post["can.vehicle.speed"] : null;
			$lacak->can_webasto_status  = isset($post["can.webasto.status"]) ? $post["can.webasto.status"] : null;
			$lacak->device_id  = isset($post["device.id"]) ? $post["device.id"] : null;
			$lacak->din  = isset($post["din"]) ? $post["din"] : null;
			$lacak->din_1  = isset($post["din.1"]) ? $post["din.1"] : null;
			$lacak->din_2  = isset($post["din.2"]) ? $post["din.2"] : null;
			$lacak->din_3  = isset($post["din.3"]) ? $post["din.3"] : null;
			$lacak->dout  = isset($post["dout"]) ? $post["dout"] : null;
			$lacak->dout_1  = isset($post["dout.1"]) ? $post["dout.1"] : null;
			$lacak->dout_2  = isset($post["dout.2"]) ? $post["dout.2"] : null;
			$lacak->engine_ignition_status  = isset($post["engine.ignition.status"]) ? $post["engine.ignition.status"] : null;
			$lacak->external_powersource_voltage  = isset($post["external.powersource.voltage"]) ? $post["external.powersource.voltage"] : null;
			$lacak->gnss_state_enum  = isset($post["gnss.state.enum"]) ? $post["gnss.state.enum"] : null;
			$lacak->gnss_status  = isset($post["gnss.status"]) ? $post["gnss.status"] : null;
			$lacak->gsm_cellid  = isset($post["gsm.cellid"]) ? $post["gsm.cellid"] : null;
			$lacak->gsm_lac  = isset($post["gsm.lac"]) ? $post["gsm.lac"] : null;
			$lacak->gsm_mnc  = isset($post["gsm.mnc"]) ? $post["gsm.mnc"] : null;
			$lacak->gsm_network_roaming_status  = isset($post["gsm.network.roaming.status"]) ? $post["gsm.network.roaming.status"] : null;
			$lacak->gsm_signal_level  = isset($post["gsm.signal.level"]) ? $post["gsm.signal.level"] : null;
			$lacak->ident  = isset($post["ident"]) ? $post["ident"] : null;
			$lacak->immobilizer_keys_status  = isset($post["immobilizer.keys.status"]) ? $post["immobilizer.keys.status"] : null;
			$lacak->immobilizer_service_status  = isset($post["immobilizer.service.status"]) ? $post["immobilizer.service.status"] : null;
			$lacak->movement_status  = isset($post["movement.status"]) ? $post["movement.status"] : null;
			$lacak->position_altitude  = isset($post["position.altitude"]) ? $post["position.altitude"] : null;
			$lacak->position_direction  = isset($post["position.direction"]) ? $post["position.direction"] : null;
			$lacak->position_hdop  = isset($post["position.hdop"]) ? $post["position.hdop"] : null;
			$lacak->position_latitude  = isset($post["position.latitude"]) ? $post["position.latitude"] : null;
			$lacak->position_longitude  = isset($post["position.longitude"]) ? $post["position.longitude"] : null;
			$lacak->position_pdop  = isset($post["position.pdop"]) ? $post["position.pdop"] : null;
			$lacak->position_satellites  = isset($post["position.satellites"]) ? $post["position.satellites"] : null;
			$lacak->position_speed  = isset($post["position.speed"]) ? $post["position.speed"] : null;
			$lacak->position_valid  = isset($post["position.valid"]) ? $post["position.valid"] : null;
			$lacak->segment_can_fuel_consumed  = isset($post["segment.can.fuel.consumed"]) ? $post["segment.can.fuel.consumed"] : null;
			$lacak->segment_can_vehicle_mileage  = isset($post["segment.can.vehicle.mileage"]) ? $post["segment.can.vehicle.mileage"] : null;
			$lacak->server_timestamp  = isset($post["server.timestamp"]) ? $post["server.timestamp"] : null;
			$lacak->timestamp  = isset($post["timestamp"]) ? $post["timestamp"] : null;
			$lacak->vehicle_mileage  = isset($post["vehicle.mileage"]) ? $post["vehicle.mileage"] : null;
			$lacak->created_at = date('Y-m-d H:i:s');
			$lacak->save();
	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Created successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	public function sync_down(Request $request){
        $created_at = !empty($request->created_at) ? $request->created_at : '1900-01-01 00:00:00';
        $limit = !empty($request->limit) ? $request->limit : 1000;
    	$list = Lacak::where('created_at', '>=', $created_at)
    		->orderBy('created_at', 'ASC')
    		->limit($limit)
    		->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
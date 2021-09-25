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
	    	$lacak->ain_1 = $post ["ain.1"];
			$lacak->ain_2 = $post ["ain.2"];
			$lacak->battery_charging_status = $post ["battery.charging.status"];
			$lacak->battery_current = $post ["battery.current"];
			$lacak->battery_temperature = $post ["battery.temperature"];
			$lacak->battery_voltage = $post ["battery.voltage"];
			$lacak->button_pressed_status = $post ["button.pressed.status"];
			$lacak->cable_connected_status = $post ["cable.connected.status"];
			$lacak->can_battery_voltage = $post ["can.battery.voltage"];
			$lacak->can_car_closed_remote_status = $post ["can.car.closed.remote.status"];
			$lacak->can_car_closed_status = $post ["can.car.closed.status"];
			$lacak->can_connection_state_1 = $post ["can.connection.state.1"];
			$lacak->can_connection_state_2 = $post ["can.connection.state.2"];
			$lacak->can_connection_state_3 = $post ["can.connection.state.3"];
			$lacak->can_driver_door_status = $post ["can.driver.door.status"];
			$lacak->can_dynamic_ignition_status = $post ["can.dynamic.ignition.status"];
			$lacak->can_engine_ignition_status = $post ["can.engine.ignition.status"];
			$lacak->can_engine_load_level = $post ["can.engine.load.level"];
			$lacak->can_engine_motorhours = $post ["can.engine.motorhours"];
			$lacak->can_engine_rpm = $post ["can.engine.rpm"];
			$lacak->can_engine_temperature = $post ["can.engine.temperature"];
			$lacak->can_engine_working_status = $post ["can.engine.working.status"];
			$lacak->can_fuel_consumed = $post ["can.fuel.consumed"];
			$lacak->can_fuel_level = $post ["can.fuel.level"];
			$lacak->can_fuel_volume = $post ["can.fuel.volume"];
			$lacak->can_handbrake_status = $post ["can.handbrake.status"];
			$lacak->can_hood_status = $post ["can.hood.status"];
			$lacak->can_ignition_key_status = $post ["can.ignition.key.status"];
			$lacak->can_lvc_module_control_bitmask = $post ["can.lvc.module.control.bitmask"];
			$lacak->can_module_id = $post ["can.module.id"];
			$lacak->can_module_sleep_mode = $post ["can.module.sleep.mode"];
			$lacak->can_parking_status = $post ["can.parking.status"];
			$lacak->can_passenger_door_status = $post ["can.passenger.door.status"];
			$lacak->can_pedal_brake_status = $post ["can.pedal.brake.status"];
			$lacak->can_program_id = $post ["can.program.id"];
			$lacak->can_rear_left_door_status = $post ["can.rear.left.door.status"];
			$lacak->can_rear_right_door_status = $post ["can.rear.right.door.status"];
			$lacak->can_reverse_gear_status = $post ["can.reverse.gear.status"];
			$lacak->can_throttle_pedal_level = $post ["can.throttle.pedal.level"];
			$lacak->can_tracker_counted_mileage = $post ["can.tracker.counted.mileage"];
			$lacak->can_trunk_status = $post ["can.trunk.status"];
			$lacak->can_vehicle_battery_level = $post ["can.vehicle.battery.level"];
			$lacak->can_vehicle_mileage = $post ["can.vehicle.mileage"];
			$lacak->can_vehicle_speed = $post ["can.vehicle.speed"];
			$lacak->can_webasto_status = $post ["can.webasto.status"];
			$lacak->device_id = $post ["device.id"];
			$lacak->din = $post ["din"];
			$lacak->din_1 = $post ["din.1"];
			$lacak->din_2 = $post ["din.2"];
			$lacak->din_3 = $post ["din.3"];
			$lacak->dout = $post ["dout"];
			$lacak->dout_1 = $post ["dout.1"];
			$lacak->dout_2 = $post ["dout.2"];
			$lacak->engine_ignition_status = $post ["engine.ignition.status"];
			$lacak->external_powersource_voltage = $post ["external.powersource.voltage"];
			$lacak->gnss_state_enum = $post ["gnss.state.enum"];
			$lacak->gnss_status = $post ["gnss.status"];
			$lacak->gsm_cellid = $post ["gsm.cellid"];
			$lacak->gsm_lac = $post ["gsm.lac"];
			$lacak->gsm_mnc = $post ["gsm.mnc"];
			$lacak->gsm_network_roaming_status = $post ["gsm.network.roaming.status"];
			$lacak->gsm_signal_level = $post ["gsm.signal.level"];
			$lacak->ident = $post ["ident"];
			$lacak->immobilizer_keys_status = $post ["immobilizer.keys.status"];
			$lacak->immobilizer_service_status = $post ["immobilizer.service.status"];
			$lacak->movement_status = $post ["movement.status"];
			$lacak->position_altitude = $post ["position.altitude"];
			$lacak->position_direction = $post ["position.direction"];
			$lacak->position_hdop = $post ["position.hdop"];
			$lacak->position_latitude = $post ["position.latitude"];
			$lacak->position_longitude = $post ["position.longitude"];
			$lacak->position_pdop = $post ["position.pdop"];
			$lacak->position_satellites = $post ["position.satellites"];
			$lacak->position_speed = $post ["position.speed"];
			$lacak->position_valid = $post ["position.valid"];
			$lacak->segment_can_fuel_consumed = $post ["segment.can.fuel.consumed"];
			$lacak->segment_can_vehicle_mileage = $post ["segment.can.vehicle.mileage"];
			$lacak->server_timestamp = $post ["server.timestamp"];
			$lacak->timestamp = $post ["timestamp"];
			$lacak->vehicle_mileage = $post ["vehicle.mileage"];
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

    public function guard(){
        return Auth::guard('api');
    }
}
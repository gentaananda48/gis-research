<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\SystemConfiguration;
use App\Model\Lacak;

class Kernel extends ConsoleKernel
{

    protected $base_url = '';
    protected $hash = '';
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // $this->base_url = SystemConfiguration::where('code', 'LACAK_API_URL')->first(['value'])->value;
        // $this->hash = SystemConfiguration::where('code', 'LACAK_API_HASH')->first(['value'])->value;
        $schedule->call(function () {
            $this->pull_data_lacak();
        })->everyMinute();
    }

    protected function pull_data_lacak(){
        try {
            $last_data = Lacak::orderBy('created_at', 'DESC')->limit(1)->first();
            $last_created_at = $last_data == null ? '' : $last_data->created_at;
            Log::info($last_created_at);
            $base_url = 'https://ggf-vectrk-jkt01.gg-foods.com';
            $client = new Client();
            $res = $client->request('GET', $base_url.'/api/lacak/sync_down?created_at='.$last_created_at.'&limit=5000', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNzU0ODk1NywiZXhwIjoxNjQyNzMyOTU3LCJuYmYiOjE2Mzc1NDg5NTcsImp0aSI6IlI3RXR3Y05WWHdCWU10eDEiLCJzdWIiOjEsInBydiI6ImY2YjcxNTQ5ZGI4YzJjNDJiNzU4MjdhYTQ0ZjAyYjdlZTUyOWQyNGQifQ.qj6_uWB7BJlDC2_IONsZHecGo1AS-W4e7c4Zt0TE5TA",
                    'APP-VERSION' => '1.0.0'
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->data AS $k=>$v) {
                $lacak = Lacak::find($v->id);
                if($lacak==null){
                    $lacak = new Lacak();
                    $lacak->id = $v->id;
                }
                $lacak->ain_1  = isset($v->ain_1) ? $v->ain_1 : null;
                $lacak->ain_2  = isset($v->ain_2) ? $v->ain_2 : null;
                $lacak->battery_charging_status  = isset($v->battery_charging_status) ? $v->battery_charging_status : null;
                $lacak->battery_current  = isset($v->battery_current) ? $v->battery_current : null;
                $lacak->battery_temperature  = isset($v->battery_temperature) ? $v->battery_temperature : null;
                $lacak->battery_voltage  = isset($v->battery_voltage) ? $v->battery_voltage : null;
                $lacak->button_pressed_status  = isset($v->button_pressed_status) ? $v->button_pressed_status : null;
                $lacak->cable_connected_status  = isset($v->cable_connected_status) ? $v->cable_connected_status : null;
                $lacak->can_battery_voltage  = isset($v->can_battery_voltage) ? $v->can_battery_voltage : null;
                $lacak->can_car_closed_remote_status  = isset($v->can_car_closed_remote_status) ? $v->can_car_closed_remote_status : null;
                $lacak->can_car_closed_status  = isset($v->can_car_closed_status) ? $v->can_car_closed_status : null;
                $lacak->can_connection_state_1  = isset($v->can_connection_state_1) ? $v->can_connection_state_1 : null;
                $lacak->can_connection_state_2  = isset($v->can_connection_state_2) ? $v->can_connection_state_2 : null;
                $lacak->can_connection_state_3  = isset($v->can_connection_state_3) ? $v->can_connection_state_3 : null;
                $lacak->can_driver_door_status  = isset($v->can_driver_door_status) ? $v->can_driver_door_status : null;
                $lacak->can_dynamic_ignition_status  = isset($v->can_dynamic_ignition_status) ? $v->can_dynamic_ignition_status : null;
                $lacak->can_engine_ignition_status  = isset($v->can_engine_ignition_status) ? $v->can_engine_ignition_status : null;
                $lacak->can_engine_load_level  = isset($v->can_engine_load_level) ? $v->can_engine_load_level : null;
                $lacak->can_engine_motorhours  = isset($v->can_engine_motorhours) ? $v->can_engine_motorhours : null;
                $lacak->can_engine_rpm  = isset($v->can_engine_rpm) ? $v->can_engine_rpm : null;
                $lacak->can_engine_temperature  = isset($v->can_engine_temperature) ? $v->can_engine_temperature : null;
                $lacak->can_engine_working_status  = isset($v->can_engine_working_status) ? $v->can_engine_working_status : null;
                $lacak->can_fuel_consumed  = isset($v->can_fuel_consumed) ? $v->can_fuel_consumed : null;
                $lacak->can_fuel_level  = isset($v->can_fuel_level) ? $v->can_fuel_level : null;
                $lacak->can_fuel_volume  = isset($v->can_fuel_volume) ? $v->can_fuel_volume : null;
                $lacak->can_handbrake_status  = isset($v->can_handbrake_status) ? $v->can_handbrake_status : null;
                $lacak->can_hood_status  = isset($v->can_hood_status) ? $v->can_hood_status : null;
                $lacak->can_ignition_key_status  = isset($v->can_ignition_key_status) ? $v->can_ignition_key_status : null;
                $lacak->can_lvc_module_control_bitmask  = isset($v->can_lvc_module_control_bitmask) ? $v->can_lvc_module_control_bitmask : null;
                $lacak->can_module_id  = isset($v->can_module_id) ? $v->can_module_id : null;
                $lacak->can_module_sleep_mode  = isset($v->can_module_sleep_mode) ? $v->can_module_sleep_mode : null;
                $lacak->can_parking_status  = isset($v->can_parking_status) ? $v->can_parking_status : null;
                $lacak->can_passenger_door_status  = isset($v->can_passenger_door_status) ? $v->can_passenger_door_status : null;
                $lacak->can_pedal_brake_status  = isset($v->can_pedal_brake_status) ? $v->can_pedal_brake_status : null;
                $lacak->can_program_id  = isset($v->can_program_id) ? $v->can_program_id : null;
                $lacak->can_rear_left_door_status  = isset($v->can_rear_left_door_status) ? $v->can_rear_left_door_status : null;
                $lacak->can_rear_right_door_status  = isset($v->can_rear_right_door_status) ? $v->can_rear_right_door_status : null;
                $lacak->can_reverse_gear_status  = isset($v->can_reverse_gear_status) ? $v->can_reverse_gear_status : null;
                $lacak->can_throttle_pedal_level  = isset($v->can_throttle_pedal_level) ? $v->can_throttle_pedal_level : null;
                $lacak->can_tracker_counted_mileage  = isset($v->can_tracker_counted_mileage) ? $v->can_tracker_counted_mileage : null;
                $lacak->can_trunk_status  = isset($v->can_trunk_status) ? $v->can_trunk_status : null;
                $lacak->can_vehicle_battery_level  = isset($v->can_vehicle_battery_level) ? $v->can_vehicle_battery_level : null;
                $lacak->can_vehicle_mileage  = isset($v->can_vehicle_mileage) ? $v->can_vehicle_mileage : null;
                $lacak->can_vehicle_speed  = isset($v->can_vehicle_speed) ? $v->can_vehicle_speed : null;
                $lacak->can_webasto_status  = isset($v->can_webasto_status) ? $v->can_webasto_status : null;
                $lacak->device_id  = isset($v->device_id) ? $v->device_id : null;
                $lacak->din  = isset($v->din) ? $v->din : null;
                $lacak->din_1  = isset($v->din_1) ? $v->din_1 : null;
                $lacak->din_2  = isset($v->din_2) ? $v->din_2 : null;
                $lacak->din_3  = isset($v->din_3) ? $v->din_3 : null;
                $lacak->dout  = isset($v->dout) ? $v->dout : null;
                $lacak->dout_1  = isset($v->dout_1) ? $v->dout_1 : null;
                $lacak->dout_2  = isset($v->dout_2) ? $v->dout_2 : null;
                $lacak->engine_ignition_status  = isset($v->engine_ignition_status) ? $v->engine_ignition_status : null;
                $lacak->external_powersource_voltage  = isset($v->external_powersource_voltage) ? $v->external_powersource_voltage : null;
                $lacak->gnss_state_enum  = isset($v->gnss_state_enum) ? $v->gnss_state_enum : null;
                $lacak->gnss_status  = isset($v->gnss_status) ? $v->gnss_status : null;
                $lacak->gsm_cellid  = isset($v->gsm_cellid) ? $v->gsm_cellid : null;
                $lacak->gsm_lac  = isset($v->gsm_lac) ? $v->gsm_lac : null;
                $lacak->gsm_mnc  = isset($v->gsm_mnc) ? $v->gsm_mnc : null;
                $lacak->gsm_network_roaming_status  = isset($v->gsm_network_roaming_status) ? $v->gsm_network_roaming_status : null;
                $lacak->gsm_signal_level  = isset($v->gsm_signal_level) ? $v->gsm_signal_level : null;
                $lacak->ident  = isset($v->ident) ? $v->ident : null;
                $lacak->immobilizer_keys_status  = isset($v->immobilizer_keys_status) ? $v->immobilizer_keys_status : null;
                $lacak->immobilizer_service_status  = isset($v->immobilizer_service_status) ? $v->immobilizer_service_status : null;
                $lacak->movement_status  = isset($v->movement_status) ? $v->movement_status : null;
                $lacak->position_altitude  = isset($v->position_altitude) ? $v->position_altitude : null;
                $lacak->position_direction  = isset($v->position_direction) ? $v->position_direction : null;
                $lacak->position_hdop  = isset($v->position_hdop) ? $v->position_hdop : null;
                $lacak->position_latitude  = isset($v->position_latitude) ? $v->position_latitude : null;
                $lacak->position_longitude  = isset($v->position_longitude) ? $v->position_longitude : null;
                $lacak->position_pdop  = isset($v->position_pdop) ? $v->position_pdop : null;
                $lacak->position_satellites  = isset($v->position_satellites) ? $v->position_satellites : null;
                $lacak->position_speed  = isset($v->position_speed) ? $v->position_speed : null;
                $lacak->position_valid  = isset($v->position_valid) ? $v->position_valid : null;
                $lacak->segment_can_fuel_consumed  = isset($v->segment_can_fuel_consumed) ? $v->segment_can_fuel_consumed : null;
                $lacak->segment_can_vehicle_mileage  = isset($v->segment_can_vehicle_mileage) ? $v->segment_can_vehicle_mileage : null;
                $lacak->server_timestamp  = isset($v->server_timestamp) ? $v->server_timestamp : null;
                $lacak->timestamp  = isset($v->timestamp) ? $v->timestamp : null;
                $lacak->vehicle_mileage  = isset($v->vehicle_mileage) ? $v->vehicle_mileage : null;
                $lacak->created_at = isset($v->created_at) ? $v->created_at : null; 
                $lacak->save();
            }
            Log::info('Pull Data Lacak');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

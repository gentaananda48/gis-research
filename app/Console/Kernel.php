<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\SystemConfiguration;
use App\Model\Tracker;

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
        $this->base_url = SystemConfiguration::where('code', 'LACAK_API_URL')->first(['value'])->value;
        $this->hash = SystemConfiguration::where('code', 'LACAK_API_HASH')->first(['value'])->value;
        $schedule->call(function () {
            $this->pull_data_gps();
            sleep(30);
            $this->pull_data_gps();
        })->everyMinute();

        $schedule->call(function () {
            $this->pull_data_sensor();
            sleep(30);
            $this->pull_data_sensor();
        })->everyMinute();
    }

    protected function pull_data_gps(){
        try {
            $list_unit = Unit::get(['lacak_id']);
            $list_lacak_id = [];
            foreach($list_unit AS $v){
                $list_lacak_id[] = $v->lacak_id;
            }
            $trackers = '['.join(',',$list_lacak_id).']';
            $client = new Client();
            $res = $client->request('POST', $this->base_url.'/tracker/get_states', [
                'form_params' => [
                    'hash'      => $this->hash,
                    'trackers'  => $trackers
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->states AS $k=>$v) {
                $tracker = Tracker::where('tracker_id', $k)->where('updated', $v->gps->updated)->first();
                if($tracker==null){
                    $tracker = new Tracker;
                    $tracker->tracker_id    = $k;
                    $tracker->updated       = $v->gps->updated;
                }
                $tracker->signal_level      = $v->gps->signal_level;
                $tracker->latitude          = $v->gps->location->lat;
                $tracker->longitude         = $v->gps->location->lng;
                $tracker->heading           = $v->gps->heading;
                $tracker->speed             = $v->gps->speed;
                $tracker->altitude          = $v->gps->alt;
                $tracker->movement_status   = $v->movement_status;
                $tracker->save();
            }
            Log::info('Pull Data GPS');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function pull_data_sensor(){
        try {
            $list_unit = Unit::get(['lacak_id']);
            $list_lacak_id = [];
            foreach($list_unit AS $v){
                $client = new Client();
                $res = $client->request('POST', $this->base_url.'/tracker/get_readings', [
                    'form_params' => [
                        'hash'          => $this->hash,
                        'tracker_id'    => $v->lacak_id
                    ]
                ]);
                $body = json_decode($res->getBody());
                foreach($body->inputs AS $v2) {
                    if($v2->name=='analog_1') {
                        $water_pressure_kanan = $v2->value;
                    } else if($v2->name=='analog_2') {
                        $water_pressure_kiri = $v2->value;
                    }
                }
                $tracker = Tracker::where('tracker_id', $v->lacak_id)->where('updated', $body->update_time)->first();
                if($tracker==null){
                    $tracker = new Tracker;
                    $tracker->tracker_id    = $v->lacak_id;
                    $tracker->updated       = $body->update_time;
                }
                $tracker->nozzle_kanan = $water_pressure_kanan;
                $tracker->nozzle_kiri = $water_pressure_kiri;
                $tracker->save();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        Log::info('Pull Data Sensor');
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

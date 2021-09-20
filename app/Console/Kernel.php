<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\Tracker;

class Kernel extends ConsoleKernel
{
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
        $schedule->call(function () {
            $base_url = 'https://api.lacak.io';
            $hash = '375f851d60cb30450125d5414c6b76c7';
            try {
                $list_unit = Unit::get(['lacak_id']);
                $list_lacak_id = [];
                foreach($list_unit AS $v){
                    $list_lacak_id[] = $v->lacak_id;
                }
                $trackers = '['.join(',',$list_lacak_id).']';
                $client = new Client();
                $res = $client->request('POST', $base_url.'/tracker/get_states', [
                    'form_params' => [
                        'hash'      => $hash,
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
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
            Log::info('TEST COMMAND');
        })->everyMinute();
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class ApiCronJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:api-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hit api for run save json in cms';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $produrl = 'https://spraying.ggfsystem.com/api/microcontroller/cron-json';
            $devurl = 'http://127.0.0.1:8000/api/microcontroller/cron-json';
            $headers = [
                'Content-Type' => 'application/json',
                'APP-VERSION' => '1.0.17',
            ];

            $client = new Client([
                'headers' => $headers
            ]);

            $res = $client->request('GET', $devurl);
            $body = json_decode($res->getBody());
            if ($body->status) {
                Log::info("save json file via api success");
                $this->info("save json file via api success");
            }else{
                Log::info("save json file via api failed");
                $this->info("save json file via api failed");
            }
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

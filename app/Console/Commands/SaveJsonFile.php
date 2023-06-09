<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class SaveJsonFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:jsonfile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto save list json file from mobile';

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
        $path = public_path('upload/database');
        $files = File::files($path);
        if (count($files) > 0) {
            // \Log::info($files[0]);
            foreach($files as $tempFile){
                $pathArchive = public_path('upload/archive');

                if (!file_exists($pathArchive)) {
                    mkdir($pathArchive, 0777, true);
                }

                $year = date('Y');
                $month = date('m');
                $date = date('d');
                $pathArchiveMonth = public_path('upload/archive/'.$year.$month.$date);

                if (!file_exists($pathArchiveMonth)) {
                    mkdir($pathArchiveMonth, 0777, true);
                }

                // get name from file
                $name = basename($tempFile);
                $getData = json_decode(file_get_contents($tempFile));

                try {
                    if ($getData) {
                        foreach ($getData as $data) {
                            $cekTable = \DB::table("lacak_".$data->source_device_id)->where('device_timestamp',$data->utc_timestamp_tablet)->first();
                            if($cekTable){
                                    continue;
                            }
    
                            $temp['utc_timestamp'] = $data->utc_timestamp ? $data->utc_timestamp:null;
                            if (isset($data->utc_timestamp_tablet)) {
                                $temp['device_timestamp'] = $data->utc_timestamp_tablet ? $data->utc_timestamp_tablet:null;
                            }

                            $temp['microcontroller_id'] = $data->microcontroller_id ? $data->microcontroller_id:null;
                            $temp['latitude'] = $data->latitude ? $data->latitude:null;
                            $temp['longitude'] = $data->longitude ? $data->longitude:null;
                            $temp['speed'] = $data->speed ? $data->speed:null;
                            $temp['altitude'] = $data->altitude ? $data->altitude:null;
                            $temp['arm_height_left'] = $data->arm_height_left ? $data->arm_height_left:null;
                            $temp['arm_height_right'] = $data->arm_height_right ? $data->arm_height_right:null;
                            $temp['temperature_left'] = $data->temperature_left ? $data->temperature_left:null;
                            $temp['temperature_right'] = $data->temperature_right ? $data->temperature_right:null;
                            $temp['pump_switch_left'] = $data->pump_switch_left ? $data->pump_switch_left:0;
                            $temp['pump_switch_right'] = $data->pump_switch_right ? $data->pump_switch_right:0;
                            $temp['pump_switch_main'] = $data->pump_switch_main ? $data->pump_switch_main:0;
                            $temp['flow_meter_left'] = $data->flow_meter_left ? $data->flow_meter_left:null;
                            $temp['flow_meter_right'] = $data->flow_meter_right ? $data->flow_meter_right:null;
                            $temp['tank_level'] = $data->tank_level ? $data->tank_level:null;
                            $temp['oil'] = $data->oil ? $data->oil:null;
                            $temp['gas'] = $data->gas ? $data->gas:null;
                            $temp['homogenity'] = $data->homogenity ? $data->homogenity:null;
                            $temp['bearing'] = isset($data->bearing) ? $data->bearing:null;
                            $temp['box_id'] = $data->box_id ? $data->box_id:null;
                            $temp['unit_label'] = $data->unit_label ? $data->unit_label:null;
                            $temp['created_at'] = date('Y-m-d H:i:s');
                            $temp['processed'] = 0;
                            $report_date = date('His', $data->utc_timestamp) <= '050000' ? date('Y-m-d', strtotime("-1 day", $data->utc_timestamp)) : date('Y-m-d', $data->utc_timestamp);
                            $temp['report_date'] = $report_date;
    
                            \DB::table("lacak_".$data->source_device_id)->insert($temp);
                        }
    
                        // pindahkan dari publik database ke archive
                        File::move($path.'/'.$name, $pathArchiveMonth.'/'.$name);

                        \Log::info("save success");
                        $this->info('save succes');
                    }
                } catch (\Throwable $th) {
                    \Log::info($th->getMessage());
                    \Log::info($th->getLine());
                    $this->info($th->getMessage());
                    $this->info($th->getLine());
                }
            }
        }
    }
}

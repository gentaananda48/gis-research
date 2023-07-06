<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportParameterStandardDetail;
use App\Model\RencanaKerja;


class DataPercentage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:listpercentage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and cache the list_percentage data';

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
        $oldLimit = ini_get('memory_limit');
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $today = date('Y-m-d');
        $list_rk = RencanaKerja::whereDate('created_at', $today)->get();

        if ($list_rk->isEmpty()) {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $list_rk = RencanaKerja::whereDate('created_at', $yesterday)->get();
        }

        $cache = [];

        foreach ($list_rk as $rk) {
            $cache_key = env('APP_CODE') . ':REPORT_PERCENTAGE_RITASE_' . $rk->id;

            if (isset($cache[$cache_key])) {
                continue;
            }

            $standard = [
                'speed_range_1' => -999999,
                'speed_range_2' => 999999,
                'arm_height_left_range_1' => -999999,
                'arm_height_left_range_2' => 999999,
                'arm_height_right_range_1' => -999999,
                'arm_height_right_range_2' => 999999
            ];

            $rpsd_speed = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
                ->where('rps.aktivitas_id', $rk->aktivitas_id)
                ->where('rps.nozzle_id', $rk->nozzle_id)
                ->where('rps.volume_id', $rk->volume_id)
                ->where('report_parameter_standard_detail.report_parameter_id', 1)
                ->where('report_parameter_standard_detail.point', 1)
                ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);

            if ($rpsd_speed) {
                $standard['speed_range_1'] = doubleval($rpsd_speed->range_1);
                $standard['speed_range_2'] = doubleval($rpsd_speed->range_2);
            }

            $rpsd_arm_height_left = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
                ->where('rps.aktivitas_id', $rk->aktivitas_id)
                ->where('rps.nozzle_id', $rk->nozzle_id)
                ->where('rps.volume_id', $rk->volume_id)
                ->where('report_parameter_standard_detail.report_parameter_id', 4)
                ->where('report_parameter_standard_detail.point', 1)
                ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);

            if ($rpsd_arm_height_left) {
                $standard['arm_height_left_range_1'] = doubleval($rpsd_arm_height_left->range_1);
                $standard['arm_height_left_range_2'] = doubleval($rpsd_arm_height_left->range_2);
            }

            $rpsd_arm_height_right = ReportParameterStandardDetail::join('report_parameter_standard AS rps', 'rps.id', '=', 'report_parameter_standard_detail.report_parameter_standard_id')
                ->where('rps.aktivitas_id', $rk->aktivitas_id)
                ->where('rps.nozzle_id', $rk->nozzle_id)
                ->where('rps.volume_id', $rk->volume_id)
                ->where('report_parameter_standard_detail.report_parameter_id', 5)
                ->where('report_parameter_standard_detail.point', 1)
                ->first(['report_parameter_standard_detail.range_1', 'report_parameter_standard_detail.range_2']);

            if ($rpsd_arm_height_right) {
                $standard['arm_height_right_range_1'] = doubleval($rpsd_arm_height_right->range_1);
                $standard['arm_height_right_range_2'] = doubleval($rpsd_arm_height_right->range_2);
            }

            if (!isset($cache[$cache_key])) {
                $list_percentage = DB::select("CALL get_report_percentage_ritase(" . $rk->id . "," . $standard['speed_range_1'] . "," . $standard['speed_range_2'] . "," . $standard['arm_height_right_range_1'] . "," . $standard['arm_height_right_range_2'] . "," . $standard['arm_height_left_range_1'] . "," . $standard['arm_height_left_range_2'] . ")");

                if (!empty($list_percentage)) {
                    Redis::set($cache_key, json_encode($list_percentage), 'EX', 2592000);
                    $this->info('List percentage data generated and cached successfully for RencanaKerja ID ' . $rk->id);
                }

                $cache[$cache_key] = true;
            }

            $this->info('List percentage data generated and cached successfully for RencanaKerja ID ' . $rk->id);

            ini_set('memory_limit', $oldLimit);
        }
    }

}

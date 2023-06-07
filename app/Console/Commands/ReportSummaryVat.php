<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use GuzzleHttp\Client;

use App\Model\Unit;
use App\Model\SystemConfiguration;
use App\Model\Lacak;
use App\Model\Lacak2;
use App\Helper\GeofenceHelper;
use App\Model\KoordinatLokasi;
use App\Model\RencanaKerja;
use App\Model\ReportParameter;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportStatus;
use App\Model\Aktivitas;
use App\Model\ReportRencanaKerja;
use App\Model\VReportRencanaKerja;
use App\Model\VReportRencanaKerja2;
use App\Model\CloneAllRencanaKerja;


class ReportSummaryVat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summary Report VAT';

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
        $oldLimit = ini_get( 'memory_limit' );
        ini_set( 'memory_limit', '-1' );
        set_time_limit(0);

        $today = date('Y-m-d');
        $list_rks = RencanaKerjaSummary::whereDate('created_at', $today)->get();

        if ($list_rks->isEmpty()) {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $list_rks = RencanaKerjaSummary::whereDate('created_at', $yesterday)->get();
        }

        foreach ($list_rks as $rks) {
            $cache_key = env('APP_CODE') . ':RK_SUMMARY_' . $rks->rk_id;
            $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rks->rk_id)->get()->toArray();
            $header = [];
            $rata2 = [];
            $poin = [];
            $kualitas = '-';

            foreach ($list_rks as $rks2) {
                if ($rks2->ritase == 999) {
                    $header[$rks2->parameter_id] = $rks2->parameter_nama;
                    $rata2[$rks2->parameter_id] = $rks2->parameter_id != 2 ? number_format($rks2->realisasi, 2) : $rks2->realisasi;
                    $poin[$rks2->parameter_id] = $rks2->nilai_bobot;
                } elseif ($rks2->ritase == 999999) {
                    $poin[999] = $rks2->nilai_bobot;
                    $kualitas = $rks2->kualitas;
                }
            }

            $summary = (object) [
                'header'    => $header,
                'ritase'    => $list_rrk,
                'rata2'     => $rata2,
                'poin'      => $poin,
                'kualitas'  => $kualitas
            ];

            Redis::set($cache_key, json_encode($summary), 'EX', 2592000);
        }

        $this->info('Summary data generated and stored in Redis.');
                
        ini_set( 'memory_limit', $oldLimit );

    }
     
}

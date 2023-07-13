<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RencanaKerjaController;

class GenerateRencanaKerjaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Rencana Kerja Report';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    protected $rkController;

      public function __construct(RencanaKerjaController $rkController)
    {
        parent::__construct();
        $this->rkController = $rkController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $result = $this->rkController->generate_rencana_kerja_report();
        $this->info($result);
    }
}

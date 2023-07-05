<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReport;

class SendEmailReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily email report VAT';

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
        // Fetch data from the database using a SQL query
        $data = DB::select('SELECT tanggal, pg, unit, shift, lokasi, activity, speed_standar, wing_kiri_standar, wing_kanan_standar FROM report_conformities WHERE tanggal = CURDATE() - 1 GROUP BY pg');

        // Prepare the email data
        $emailData = [
            'tableHeaders' => [
                'no', 'unit', 'shift', 'location', 'activity',
                'rencana kerja', 'speed on standard',
                'wing left on standard', 'wing right on standard'
            ],
            'tableData' => $data
        ];

        // Send the email
        Mail::to('akhmad.hidayat@gg-foods.com')->send(new DailyReport($emailData));

        $this->info('Daily report email sent successfully.');
    }
}

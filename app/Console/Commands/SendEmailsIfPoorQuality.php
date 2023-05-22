<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PDF;

class SendEmailsIfPoorQuality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:poor-quality-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email with a PDF report if the quality is poor';

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
        // get data kualitas from database with name of pg and number of unit
        $today = now()->format('Y-m-d');
        $data = DB::table('report_rencana_kerja')
                ->select('nama_aktivitas', 'nama_unit', 'rencana_kerja_summary.kualitas')
                ->leftJoin('rencana_kerja_summary', 'report_rencana_kerja.rencana_kerja_id', '=', 'rencana_kerja_summary.rk_id')
                ->where('rencana_kerja_summary.kualitas', '=', 'poor')
                ->whereDate('report_rencana_kerja.created_at', $today)
                ->get();
        
            \Log::info($data);

        if (count($data) > 0) {
            $pdf = PDF::loadView('pdf.report', ['data' => $data]);
            $pdf->save(storage_path('app/report.pdf'));

            $body = 'Dear recipient,\n\nPlease find attached the report for the poor quality data.\n\nBest regards,\nThe Sender';

            Mail::send([], [], function ($message) use ($body) {
                $message->to('fahmirbbn@gmail.com')
                    ->subject('Poor Quality Report')
                    ->setBody($body, 'text/plain')
                    ->attach(storage_path('app/report.pdf'), [
                        'as' => 'report.pdf',
                        'mime' => 'application/pdf',
                    ]);
            });

            Storage::delete('report.pdf');            

            $this->info('Email sent.');
        } else {
            $this->info('No poor quality data found.');
        }
    }
}

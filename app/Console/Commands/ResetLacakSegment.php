<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Helper\GeofenceHelper;
use Illuminate\Support\Facades\Redis;
use App\Model\Unit;
use App\Model\KoordinatLokasi;
use App\Model\CronLog;
use App\Helper\CronLogHelper;

class ResetLacakSegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:lacak-segment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset Lacak Segment dont use if not urgently';

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
    public function handle() {
        $cron_helper = new CronLogHelper;
        // $cron_helper->create('process:lacak-segment', 'STARTED', 'SourceDeviceID: '.$unit);
        $units = Unit::pluck('label')->all();
        DB::beginTransaction();
        try {
            foreach($units as $source_device_id) {
                $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($source_device_id)));
                $this->info('table: '.$table_name);
                DB::table($table_name)
                        ->select("*")
                        ->where('is_segment',1)
                        ->update([
                            'is_segment' => 0
                        ]);
                $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
                        
                // cek lacak segment
                DB::table($table_segment_label)->truncate();

                DB::commit();
                // end overlapping
                $this->info('Success reset data table segment: '.$table_segment_label);         
            }
        } catch (\Exception $e) {
            DB::rollback(); 
            Log::error($e->getMessage());
            dd($e->getMessage());
        }
    }
}

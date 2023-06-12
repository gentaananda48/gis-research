<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SumarySegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:sumary-segment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'input data from lacak segment to summary';

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
        DB::beginTransaction();
        try {
            $data = DB::select(DB::raw("
            select 
            segment,
            lacak_BSC_01.unit_label,
            kode_lokasi,
            MAX(lacak_segment_BSC_01.created_at) as created_date,
            round(AVG(speed),2) as avg_speed,
            round(SUM(luasan_m2),2) as total_luasan,
            count(lacak_segment_BSC_01.id) as total_data_point
            from lacak_segment_BSC_01
            LEFT JOIN lacak_BSC_01 on lacak_BSC_01.id = lacak_segment_BSC_01.lacak_bsc_id
            GROUP BY unit_label,segment,kode_lokasi
            "));

            if (count($data) > 0) {
                foreach ($data as $key => $value) {
                    DB::insert("INSERT INTO summary_segments (
                        unit, 
                        segment, 
                        lokasi, 
                        total_luasan,
                        created_date, 
                        avg_speed, 
                        speed_dibawah_standar,
                        speed_standar,
                        speed_diatas_standar,
                        avg_wing_kiri,
                        wing_kiri_dibawah_standar,
                        wing_kiri_standar,
                        wing_kiri_diatas_standar,
                        avg_wing_kanan,
                        wing_kanan_dibawah_standar,
                        wing_kanan_standar,
                        wing_kanan_diatas_standar,
                        avg_goldentime,
                        goldentime_standar,
                        goldentime_tidak_standar,
                        avg_spray,
                        spray_standar,
                        spray_tidak_standar,
                        total_data_point
                        ) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
                        [$value->unit_label,
                        $value->segment,
                        $value->kode_lokasi,
                        $value->total_luasan, 
                        $value->created_date,
                        (float) $value->avg_speed,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        0,
                        $value->total_data_point]
                    );

                    DB::commit();
                    $this->info('Success inputing data to table summary segment');
                }
            }else {
                $this->info('Gagal Input data');
            }
            
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

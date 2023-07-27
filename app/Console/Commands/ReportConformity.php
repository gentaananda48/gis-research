<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportConformity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:report-conformity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Input from sumary segment';

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
            // truncate table
            DB::table('report_conformities')->truncate();
            DB::commit();
            // truncate

            $data = DB::select(DB::raw("
            WITH tb1 AS (
                SELECT 
                  CAST(created_date as DATE) as created_date, 
                  lokasi, 
                  unit,
                    SUM(avg_speed * total_data_point)/SUM(total_data_point) as total_speed,
                    SUM(speed_dibawah_standar * total_data_point)/SUM(total_data_point) as total_speed_dibawah_standar,
                    SUM(speed_standar * total_data_point)/SUM(total_data_point) as total_speed_standar,
                    SUM(speed_diatas_standar * total_data_point)/SUM(total_data_point) as total_speed_diatas_standar,
                    SUM(avg_wing_kiri * total_data_point)/SUM(total_data_point) as total_wing_kiri,
                    SUM(wing_kiri_dibawah_standar * total_data_point)/SUM(total_data_point) as total_wing_kiri_dibawah_standar,
                    SUM(wing_kiri_standar * total_data_point)/SUM(total_data_point) as total_wing_kiri_standar,
                    SUM(wing_kiri_diatas_standar * total_data_point)/SUM(total_data_point) as total_wing_kiri_diatas_standar,
                    SUM(avg_wing_kanan * total_data_point)/SUM(total_data_point) as total_wing_kanan,
                    SUM(wing_kanan_dibawah_standar * total_data_point)/SUM(total_data_point) as total_wing_kanan_dibawah_standar,
                    SUM(wing_kanan_standar * total_data_point)/SUM(total_data_point) as total_wing_kanan_standar,
                    SUM(wing_kanan_diatas_standar * total_data_point)/SUM(total_data_point) as total_wing_kanan_diatas_standar,
                    SUM(avg_goldentime * total_data_point)/SUM(total_data_point) as total_goldentime,
                    SUM(goldentime_tidak_standar * total_data_point)/SUM(total_data_point) as total_goldentime_dibawah_standar,
                    SUM(goldentime_standar * total_data_point)/SUM(total_data_point) as total_goldentime_standar,
                    SUM(suhu_tidak_standar * total_data_point)/SUM(total_data_point) as total_suhu_dibawah_standar,
                    SUM(suhu_standar * total_data_point)/SUM(total_data_point) as total_suhu_standar,
                    round(SUM(total_luasan), 2) as total_luasan_sum,
                    round(SUM(total_spraying), 2) as total_spraying_sum,
                    round(SUM(total_overlaping), 2) as total_overlaping_sum
                FROM summary_segments
                  GROUP BY lokasi,CAST(created_date AS DATE),unit
              ),
              tb2 AS (
                SELECT tgl, lokasi_kode, unit_label, shift_nama, lokasi_grup, aktivitas_nama
                FROM rencana_kerja
              ),
              tb3 AS (
                SELECT wilayah, kode
                FROM lokasi
              )
              SELECT 
              tb2.lokasi_grup,
              tb3.wilayah,
              tb2.aktivitas_nama as aktivitas,
              tb2.shift_nama as shift,
              tb1.*
              FROM tb1 
              INNER JOIN tb2 ON tb2.lokasi_kode = tb1.lokasi
              INNER JOIN tb3 ON tb3.kode = tb2.lokasi_kode
              where tb1.unit = tb2.unit_label    
              and tb2.tgl = tb1.created_date          
            "));

            if (count($data) > 0) {
                foreach ($data as $key => $value) {
                    DB::insert("INSERT INTO report_conformities_temp_1 (
                        tanggal, 
                        pg, 
                        wilayah, 
                        lokasi,
                        unit,
                        activity,
                        shift,
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
                        total_luasan,
                        total_spraying,
                        total_overlaping,
                        suhu_standar,
                        suhu_tidak_standar
                        ) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
                        [$value->created_date,
                        $value->lokasi_grup,
                        $value->wilayah,
                        $value->lokasi, 
                        $value->unit,
                        $value->aktivitas,
                        $value->shift,
                        $value->total_speed,
                        $value->total_speed_dibawah_standar,
                        $value->total_speed_standar,
                        $value->total_speed_diatas_standar,
                        $value->total_wing_kiri,
                        $value->total_wing_kiri_dibawah_standar,
                        $value->total_wing_kiri_standar,
                        $value->total_wing_kiri_diatas_standar,
                        $value->total_wing_kanan,
                        $value->total_wing_kanan_dibawah_standar,
                        $value->total_wing_kanan_standar,
                        $value->total_wing_kanan_diatas_standar,
                        $value->total_goldentime,
                        $value->total_goldentime_standar,
                        $value->total_goldentime_dibawah_standar,
                        0,
                        0,
                        0,
                        $value->total_luasan_sum,
                        $value->total_spraying_sum,
                        $value->total_overlaping_sum,
                        $value->total_suhu_standar,
                        $value->total_suhu_dibawah_standar
                        ]
                    );

                    DB::commit();
                    $this->info('Success inputing data to table report conformities');
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

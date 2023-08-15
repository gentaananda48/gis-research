<?php

namespace App\Console\Commands;

use App\Model\Unit;
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
            $units = Unit::pluck('label')->all();
            // truncate table
            DB::table('summary_segments')->truncate();
            DB::commit();
            // truncate
            foreach ($units as $source_device_id) {
                $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($source_device_id)));
                $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);

                $data = DB::select(DB::raw("
                WITH 
                    tb0 AS (
                    SELECT *
                    FROM {$table_name}
                    ),
                    tb1 AS (
                        select * from {$table_segment_label}
                        where id in (select MIN(id) from {$table_segment_label} GROUP BY lacak_bsc_id)
                    ),
                    tb2 AS (
                        SELECT *
                        FROM rencana_kerja
                        ),
                        tb3 AS (
                        SELECT *
                        FROM report_parameter_standard
                        ),
                        tb4 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE urutan = 2 and report_parameter_id = 1
                        ),
                        tb5 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE urutan = 2 and report_parameter_id = 4
                        ),
                        tb6 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE urutan = 2 and report_parameter_id = 5
                        ),
                        tb7 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE urutan = 1 and report_parameter_id = 2
                        ),
                        tb8 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE urutan = 2 and report_parameter_id = 2
                        ),
                                            tb9 AS (
                            SELECT report_parameter_standard_id,range_1,range_2
                            from report_parameter_standard_detail
                            WHERE `point` = 1 and report_parameter_id = 6
                        )
                        SELECT 
                        tb1.segment,
                        MAX(tb0.unit_label) as unit_label,
                        MAX(kode_lokasi) as kode_lokasi,
						MAX(tb1.created_at) as created_date,
                        ROUND(SUM(tb1.luasan_m2),2) as total_luasan,
                        ROUND(SUM(CASE WHEN tb1.overlapping_route = 0 THEN tb1.luasan_m2 ELSE 0 END),2) AS total_spraying,
                        ROUND(Sum(CASE WHEN tb1.overlapping_route = 1 THEN tb1.luasan_m2 ELSE 0 END),2) AS total_overlaping,
                        count(tb1.id) as total_data_point,
                        AVG(tb0.speed) as av_speed,
                        AVG(tb0.arm_height_left) as av_wing_left,
                        AVG(tb0.arm_height_right) as av_wing_right,
                        DATE_FORMAT(FROM_UNIXTIME(AVG(tb0.`utc_timestamp`)), '%H:%i:%s') as av_goldentime,
    
                        ROUND(SUM(CASE WHEN tb0.speed < tb4.range_1 THEN 1 ELSE 0 END) / COUNT(tb0.speed) * 100,2) as speed_under_standard,
                        ROUND(SUM(CASE WHEN tb0.speed BETWEEN tb4.range_1 AND tb4.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.speed) * 100,2) as speed_standard,
                        ROUND(SUM(CASE WHEN tb0.speed > tb4.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.speed) * 100,2) as speed_upper_standard,
    
                        ROUND(SUM(CASE WHEN DATE_FORMAT(FROM_UNIXTIME(tb0.`utc_timestamp`), '%H:%i:%s') BETWEEN tb8.range_1 AND tb8.range_2 THEN 0 ELSE 1 END) / COUNT(tb0.`utc_timestamp`) * 100,2) as goldentime_standard,
                        ROUND(SUM(CASE WHEN DATE_FORMAT(FROM_UNIXTIME(tb0.`utc_timestamp`), '%H:%i:%s') BETWEEN tb8.range_1 AND tb8.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.`utc_timestamp`) * 100,2) as goldentime_not_standard,
                                            
                                            ROUND(SUM(CASE WHEN tb0.temperature_right BETWEEN tb9.range_1 AND tb9.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.temperature_right) * 100,2) as suhu_standard,
                        ROUND(SUM(CASE WHEN tb0.temperature_right > tb9.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.temperature_right) * 100,2) as suhu_not_standard,
                                            
                        ROUND(SUM(CASE WHEN tb0.arm_height_left < tb5.range_1 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_left) * 100,2) as wing_left_under_standard,
                        ROUND(SUM(CASE WHEN tb0.arm_height_left BETWEEN tb5.range_1 AND tb5.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_left) * 100,2) as wing_left_standard,
                        ROUND(SUM(CASE WHEN tb0.arm_height_left > tb5.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_left) * 100,2) as wing_left_upper_standard,
    
                        ROUND(SUM(CASE WHEN tb0.arm_height_right < tb6.range_1 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_right) * 100,2) as wing_right_under_standard,
                        ROUND(SUM(CASE WHEN tb0.arm_height_right BETWEEN tb6.range_1 AND tb6.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_right) * 100,2) as wing_right_standard,
                        ROUND(SUM(CASE WHEN tb0.arm_height_right > tb6.range_2 THEN 1 ELSE 0 END) / COUNT(tb0.arm_height_right) * 100,2) as wing_right_upper_standard
                        FROM tb1 
                        
                        LEFT JOIN tb0 ON tb0.id = tb1.lacak_bsc_id
                        LEFT JOIN tb2 ON tb2.lokasi_kode = tb1.kode_lokasi
                        LEFT JOIN tb3 on tb3.aktivitas_id = tb2.aktivitas_id
                        JOIN tb4 on tb4.report_parameter_standard_id = tb3.id
                        JOIN tb5 on tb5.report_parameter_standard_id = tb3.id
                        JOIN tb6 on tb6.report_parameter_standard_id = tb3.id
                        JOIN tb7 on tb7.report_parameter_standard_id = tb3.id
                        JOIN tb8 on tb8.report_parameter_standard_id = tb3.id
                        JOIN tb9 on tb9.report_parameter_standard_id = tb3.id
                        where tb3.nozzle_id = tb2.nozzle_id
                        and tb2.tgl = DATE(tb1.created_at)
                        and tb0.speed > 0.9
                        and tb3.volume_id = tb2.volume_id
                        GROUP BY tb1.segment,DATE(tb1.created_at),kode_lokasi
                "));

                if (count($data) > 0) {
                    foreach ($data as $key => $value) {
                        if ($value->unit_label == 'BDM - 01') {
                            $unit = 'BDF - 07';
                        }else{
                            $unit = $value->unit_label;
                        }
                        DB::insert("INSERT INTO summary_segments (
                            unit, 
                            segment, 
                            lokasi, 
                            total_luasan,
                            total_spraying,
                            total_overlaping,
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
                            total_data_point,
                            suhu_standar,
                            suhu_tidak_standar
                            ) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
                            [$unit,
                            $value->segment,
                            $value->kode_lokasi,
                            $value->total_luasan,
                            $value->total_spraying, 
                            $value->total_overlaping,
                            $value->created_date,
                            $value->av_speed,
                            $value->speed_under_standard,
                            $value->speed_standard,
                            $value->speed_upper_standard,
                            $value->av_wing_left ? $value->av_wing_left:0,
                            $value->wing_left_under_standard ? $value->wing_left_under_standard:0,
                            $value->wing_left_standard ? $value->wing_left_standard:0,
                            $value->wing_left_upper_standard ? $value->wing_left_upper_standard:0,
                            $value->av_wing_right,
                            $value->wing_right_under_standard,
                            $value->wing_right_standard,
                            $value->wing_right_upper_standard,
                            0,
                            $value->goldentime_standard,
                            $value->goldentime_not_standard,
                            0,
                            0,
                            0,
                            $value->total_data_point,
                            $value->suhu_standard,
                            $value->suhu_not_standard
                            ]
                        );

                        DB::commit();
                        $this->info(now().' - Lokasi: '.$value->kode_lokasi.' Success inputing data to table summary segment');
                    }
                }else {
                    $this->info('data tidak ada');
                }
            }
            // end foreach
        } catch (\Exception $e) {
            $this->info($e->getMessage());
            DB::rollback(); 
            Log::error($e->getMessage());
        }
    }
}

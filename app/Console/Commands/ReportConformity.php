<?php

namespace App\Console\Commands;

use App\Model\RencanaKerja;
use App\Model\ReportParameterStandard;
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
                    $rk = RencanaKerja::where('unit_label', $value->unit)
                    ->where('tgl', $value->created_date)
                    ->where('lokasi_kode', $value->lokasi)
                    ->first();
                    
                $report_param_standard = ReportParameterStandard::where('volume_id', $rk->volume_id)
                    ->where('nozzle_id', $rk->nozzle_id)
                    ->where('aktivitas_id', $rk->aktivitas_id)
                    ->with([
                        'reportParameterStandarDetails' => function($query) {
                            $query->where('point', 1);
                        },
                    ])
                    ->first();
                    
                    // total 
                    $total_ancakan = (($value->total_spraying_sum/10000)/$rk->lokasi_lsnetto) * 100;

                    $table_name = "lacak_".str_replace('-', '_', str_replace(' ', '', trim($value->unit)));
                    $table_segment_label = str_replace("lacak_", "lacak_segment_", $table_name);
                    $data_bsc = \DB::table($table_name)
                    ->select($table_name.'.*',$table_segment_label.".overlapping_route")
                    ->leftJoin($table_segment_label,$table_segment_label.'.lacak_bsc_id','=',$table_name.'.id')
                    ->where('lokasi_kode',$value->lokasi)
                    ->where($table_name.'.report_date',$value->created_date);
                    
                    $data_bsc_avg = $data_bsc->avg('temperature_right');
                    $data_bsc_first = $data_bsc->first();
                    $data_bsc_last = $data_bsc->latest()->first();

                    DB::insert("INSERT INTO report_conformities (
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
                        suhu_tidak_standar,
                        start_activity,
                        end_activity,
                        waktu_spray_detik,
                        batas_suhu,
                        suhu_avg,
                        batas_atas_speed,
                        batas_bawah_speed,
                        batas_bawah_wing_level_kiri,
                        batas_atas_wing_level_kiri,
                        batas_bawah_wing_level_kanan,
                        batas_atas_wing_level_kanan,
                        total_ancakan,
                        created_at,
                        updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)", 
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
                        $value->total_suhu_dibawah_standar,
                        isset($data_bsc_first->utc_timestamp) && $data_bsc_first->utc_timestamp != null ? date('Y-m-d H:i:s',$data_bsc_first->utc_timestamp):"",
                        isset($data_bsc_last->utc_timestamp) && $data_bsc_last->utc_timestamp != null ? date('Y-m-d H:i:s',$data_bsc_last->utc_timestamp):"",
                        (isset($data_bsc_first->utc_timestamp) && $data_bsc_first->utc_timestamp != null) && isset($data_bsc_last->utc_timestamp) && $data_bsc_last->utc_timestamp != null ? $data_bsc_last->utc_timestamp - $data_bsc_first->utc_timestamp:0,
                        ($value->aktivitas == 'Forcing' || $value->aktivitas == 'Forcing 1' || $value->aktivitas == 'Forcing 2' || $value->aktivitas == 'Forcing 3') ? (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 6)->first()->range_2:"",
                        ($value->aktivitas == 'Forcing' || $value->aktivitas == 'Forcing 1' || $value->aktivitas == 'Forcing 2' || $value->aktivitas == 'Forcing 3') ? round($data_bsc_avg,2):"",
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 1)->first()->range_1,
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 1)->first()->range_2,
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 4)->first()->range_1,
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 4)->first()->range_2,
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 5)->first()->range_1,
                        (int) $report_param_standard->reportParameterStandarDetails->where('report_parameter_id', 5)->first()->range_2,
                        $total_ancakan,
                        now(),
                        now()
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

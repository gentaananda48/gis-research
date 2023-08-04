<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportConformity extends Model
{
    protected $table = 'report_conformities';

    
    public function getStandardColor($val) {
        if($val > 90)
            return 'background-color:#cbf078;';
        if($val > 70 && $val < 90)
            return 'background-color:#fcff82;';
        if( $val > 50 && $val < 70)
            return 'background-color:#f1b963;';
        
        return 'background-color:#e46161;color: white;';
    }

    protected $fillable = [
        'tanggal', 
        'pg', 
        'wilayah', 
        'lokasi',
        'unit',
        'activity',
        'shift',
        'avg_speed', 
        'speed_dibawah_standar',
        'speed_standar',
        'speed_diatas_standar',
        'avg_wing_kiri',
        'wing_kiri_dibawah_standar',
        'wing_kiri_standar',
        'wing_kiri_diatas_standar',
        'avg_wing_kanan',
        'wing_kanan_dibawah_standar',
        'wing_kanan_standar',
        'wing_kanan_diatas_standar',
        'avg_goldentime',
        'goldentime_standar',
        'goldentime_tidak_standar',
        'avg_spray',
        'spray_standar',
        'spray_tidak_standar',
        'total_luasan',
        'total_spraying',
        'total_overlaping',
        'suhu_standar',
        'suhu_tidak_standar',
        'start_activity',
        'end_activity',
        'waktu_spray_detik',
        'batas_suhu',
        'suhu_avg',
        'batas_atas_speed',
        'batas_bawah_speed',
        'batas_bawah_wing_level_kiri',
        'batas_atas_wing_level_kiri',
        'batas_bawah_wing_level_kanan',
        'batas_atas_wing_level_kanan',
        'total_ancakan',
        'created_at',
        'updated_at'
    ];
}

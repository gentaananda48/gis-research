<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SummarySegmentLuasan extends Model
{
    protected $table = 'summary_segment_luasan';

    protected $fillable = [
        'lacak_segment_id',
        'tgl',
        'pg_nama',
        'lokasi_kode',
        'unit_label',
        'luasan_m2',
        'total_luasan_m2',
        'waktu_spray',
        'speed_standard',
        'speed_dibawah_standard',
        'speed_diatas_standard',
        'avg_speed',
        'arm_height_left_standard',
        'arm_height_left_dibawah_standard',
        'arm_height_left_diatas_standard',
        'avg_height_left',
        'arm_height_right_standard',
        'arm_height_right_dibawah_standard',
        'arm_height_right_diatas_standard',
        'avg_arm_height_right',
        'temperature_standard',
        'temperature_not_standard',
        'gloden_time_good',
        'gloden_time_poor',
        'ritase'
    ];
}
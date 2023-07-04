<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use App\Model\Unit;
use App\Model\SystemConfiguration;
use App\Model\Lacak;
use App\Model\Lacak2;
use App\Helper\GeofenceHelper;
use App\Model\KoordinatLokasi;
use App\Model\RencanaKerja;
use App\Model\ReportParameter;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportStatus;
use App\Model\Aktivitas;
use App\Model\ReportRencanaKerja;
use App\Model\VReportRencanaKerja;
use App\Model\VReportRencanaKerja2;


class Kernel extends ConsoleKernel
{

    protected $base_url = '';
    protected $hash = '';
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ProcessLacakIMEI::class,
        Commands\ProcessRencanaKerja::class,
        Commands\ProcessSummaryOperational::class,
        Commands\ReportSummaryVat::class,
        Commands\SaveJsonFile::class,
        Commands\ProcessLacakSegment::class,
        Commands\SumarySegment::class,
        Commands\ReportConformity::class,
        Commands\DeleteOldArchiveFiles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        // $this->base_url = SystemConfiguration::where('code', 'LACAK_API_URL')->first(['value'])->value;
        // $this->hash = SystemConfiguration::where('code', 'LACAK_API_HASH')->first(['value'])->value;

        $schedule->call(function () {
            //$this->generate_rencana_kerja_summary();
            $this->generate_rencana_kerja_report();
        })->everyMinute();
        $schedule->call(function () {
            $this->update_kualitas_rencana_kerja();
        })->everyMinute();

        // cron for summary and delete data old in archive
        $schedule->command('summary:report')->hourly();

        //cron delete data archive more than 2 month, will execute at 00:00 (midnight) on the first day of each month.
        // $schedule->command('archive:delete-old-files')
        //      ->monthly();

        //save file json to db
        // $schedule->command('save:jsonfile')->everyMinute()->appendOutputTo(storage_path('/logs/laravel.log'));
        // \Log::info($schedule);

        // wait validation data
        // $schedule->command('process:lacak-segment')->everyMinute();
        // $schedule->command('process:sumary-segment')->everyFiveMinutes();
        // $schedule->command('process:report-conformity')->everyFiveMinutes();

        // $schedule->call(function () {
        //     $this->pull_data_lacak();
        // })->everyMinute();
    }

    public function update_kualitas_rencana_kerja(){
        $oldLimit = ini_get( 'memory_limit' );
        ini_set( 'memory_limit', '-1' );
        set_time_limit(0);
        $list_rk = RencanaKerja::
            //whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL AND (kualitas IS NULL OR kualitas = '')")
             whereRaw("status_id = 4 AND jam_laporan IS NOT NULL AND jam_laporan2 IS NULL")
            ->orderBy('id', 'ASC')
            ->limit(100)
            ->get();
        if(count($list_rk)>0){
            $list_rp = ReportParameter::orderBy('id', 'ASC')->get();
            $list_rs = ReportStatus::get();
            foreach($list_rk AS $rk) {
                $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rk->id)->get()->toArray();
                $kualitas = '';
                if(count($list_rrk)>0) {
                    $aktivitas = Aktivitas::find($rk->aktivitas_id);
                    $list_realisasi = [];
                    foreach($list_rp as $rp){
                        if($rp->id!=2){
                            $list_realisasi[$rp->id] = 0;
                        }
                    }
                    foreach($list_rrk as $k=>$v){
                        foreach($list_rp as $rp){
                            if($rp->id!=2){
                                $list_realisasi[$rp->id] += $v['parameter_'.$rp->id];
                            }
                        }
                    }
                    foreach($list_rp as $rp){
                        if($rp->id==2){
                            $list_realisasi[$rp->id] = $list_rrk[0]['parameter_'.$rp->id];
                        } else {
                            $list_realisasi[$rp->id] = $list_realisasi[$rp->id] / count($list_rrk);
                        }
                    }
                    $total_poin = 0;
                    foreach($list_rp as $rp){
                        $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                            ->where('d.report_parameter_id', $rp->id)
                            ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                            ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                            ->where('report_parameter_standard.volume_id', $rk->volume_id)
                            ->orderByRaw("d.range_1*1 ASC")
                            ->get(['d.*']);
                        $standard = '';
                        foreach($list_rps AS $rps){
                            if($rps->point==1){
                                $standard = $rps->range_1.' - '.$rps->range_2;
                            }
                        }
                        $realisasi = $list_realisasi[$rp->id];
                        $nilai = 0;
                        if($rp->id==2){
                            foreach($list_rps AS $rps){
                                $dt_nilai = date('Y-m-d '.$nilai);
                                $dt_range_1 = date('Y-m-d '.$rps->range_1);
                                if($rps->range_1 > $rps->range_2) {
                                    if($dt_nilai < $dt_range_1){
                                        $dt_nilai = date('Y-m-d '.$nilai,strtotime("+1 days"));
                                    }
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2,strtotime("+1 days"));
                                } else {
                                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                                }
                                if($dt_range_1 <= $dt_nilai && $dt_nilai <= $dt_range_2){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        } else {
                            foreach($list_rps AS $rps){
                                if(doubleval($rps->range_1) <= $realisasi && $realisasi <= doubleval($rps->range_2)){
                                    $nilai = $rps->point;
                                    break;
                                }
                            }
                        }
                        $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_UNIT')->first(['value']);
                        $offline_unit = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                        if(in_array($rk->unit_id, $offline_unit)){
                            $sysconf = SystemConfiguration::where('code', 'RPSD_NEW_BOBOT')->first(['value']);
                            $offline_bobot = !empty($sysconf->value)? explode(',', $sysconf->value) : [];
                            $bobot = !empty($offline_bobot[$rp->id-1]) ? $offline_bobot[$rp->id-1] : 0;
                        } else {
                            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $aktivitas->grup_id)
                                ->where('report_parameter_id', $rp->id)
                                ->first();
                            $bobot = !empty($rpb->bobot) ? $rpb->bobot : 0;
                        }
                        $poin = $nilai * $bobot;
                        $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                            ->where('ritase', 999)
                            ->where('parameter_id', $rp->id)
                            ->first();
                        if($rks==null){
                            $rks = new RencanaKerjaSummary;
                            $rks->rk_id = $rk->id;
                            $rks->ritase = 999;
                            $rks->parameter_id = $rp->id;
                        }
                        $rks->parameter_nama = $rp->nama;
                        $rks->standard      = $standard;
                        $rks->realisasi     = $realisasi;
                        $rks->nilai         = $nilai;
                        $rks->bobot         = $bobot;
                        $rks->nilai_bobot   = $poin;
                        $rks->kualitas      = null;
                        $rks->save();
                        $total_poin += $poin;
                    }
                    $kualitas = '';
                    foreach($list_rs as $v){
                        if(doubleval($v->range_1) <= $total_poin && $total_poin <= doubleval($v->range_2)){
                            $kualitas = $v->status;
                            break;
                        }
                    }
                    $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
                        ->where('ritase', 999999)
                        ->where('parameter_id', 999)
                        ->first();
                    if($rks==null){
                        $rks = new RencanaKerjaSummary;
                        $rks->rk_id = $rk->id;
                        $rks->ritase = 999999;
                        $rks->parameter_id = 999;
                        $rks->parameter_nama = 'Total';
                    }
                    $rks->standard      = null;
                    $rks->realisasi     = null;
                    $rks->nilai         = null;
                    $rks->bobot         = null;
                    $rks->nilai_bobot   = $total_poin;
                    $rks->kualitas      = $kualitas;
                    $rks->save();
                } else {
                    $kualitas = '-';
                }
                $rk->kualitas = $kualitas;
                $rk->jam_laporan2 = date('Y-m-d H:i:s');
                $rk->save();
            } 
        }
        ini_set( 'memory_limit', $oldLimit );
    }

    public function generate_rencana_kerja_report(){
        $oldLimit = ini_get( 'memory_limit' );
        ini_set( 'memory_limit', '-1' );
        set_time_limit(0);
        $geofenceHelper = new GeofenceHelper;
        $list_rk = RencanaKerja::
            whereRaw("status_id = 4 AND jam_laporan IS NULL")
            //where('id', $request->id)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get();
        foreach($list_rk AS $rk) {
            ReportRencanaKerja::where('rencana_kerja_id', $rk->id)->delete();
            $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);

            $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
            $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
            if(in_array($rk->unit_source_device_id, $offline_units)){
                $table_name = 'lacak_'.$rk->unit_source_device_id;
                $list = DB::table($table_name)
                    ->where('utc_timestamp', '>=', strtotime($rk->jam_mulai))
                    ->where('utc_timestamp', '<=', strtotime($rk->jam_selesai))
                    ->orderBy('utc_timestamp', 'ASC')
                    ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, 0 AS ain_1, 0 AS ain_2, pump_switch_right AS din_1, pump_switch_left AS din_2, pump_switch_main AS din_3, '' AS payload_text, `utc_timestamp` AS timestamp, arm_height_right, arm_height_left, temperature_right, temperature_left")
                    ->get();
            } else {
                if($rk->tgl>='2022-03-15') {
                    $list = Lacak2::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
                } else {
                    $list = Lacak::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
                }
            }

            $list2 = [];
            $i2 = 0;
            $list_kel = [];
            foreach($list AS $k=>$v) {
                $lokasi = count($list_polygon) > 0 ? $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude) : $rk->lokasi_kode;
                $v->waktu_tempuh = ($k==0) ? 0 : round(abs($v->timestamp - $list[$k-1]->timestamp),2);
                $v->spraying = !empty($lokasi) && $v->position_speed >= 1 && !empty($v->din_3) && (!empty($v->din_1) || !empty($v->din_2)) ? 'Y' : 'N';
                if($k>0 && $v->spraying != $list2[$k-1]->spraying) {
                    $i2++;
                }
                if(array_key_exists($i2, $list_kel)) {
                    $list_kel[$i2]->selesai        = $v->timestamp;
                    $list_kel[$i2]->waktu_tempuh   = round(abs($list_kel[$i2]->selesai - $list_kel[$i2]->mulai),2);
                    $list_kel[$i2]->waktu_tempuh2  += $v->waktu_tempuh;
                    $list_kel[$i2]->break          = $list_kel[$i2]->spraying == 'N' && $list_kel[$i2]->waktu_tempuh > 240 ? 'Y' : 'N';
                } else {
                    $list_kel[$i2] = (object) [
                        'spraying'      => $v->spraying, 
                        'mulai'         => $v->timestamp, 
                        'selesai'       => $v->timestamp, 
                        'waktu_tempuh'  => 0, 
                        'waktu_tempuh2' => $v->waktu_tempuh,
                        'break'         => 'N'
                    ];
                }
                $v->kel = $i2;
                $list2[] = $v;
            }
            $ritase = 0;
            foreach($list2 as $k=>$v){
                if($k>0){
                    if($v->spraying=='Y' && ($list_kel[$list2[$k-1]->kel]->break=='Y' || $ritase==0)) {
                        $ritase++;
                    }
                } else {
                    if($v->spraying=='Y') {
                        $ritase++;
                    }
                }
                if($list_kel[$v->kel]->break=='Y'){
                    $v->ritase = 0;
                } else {
                    $v->ritase = $ritase;
                }
                $is_overlap = 0;
                $overlapped_area = [];
                foreach($list as $key=>$point) {
                    if($k<2) break;
                    if($key>=($k-1)) break;
                    $jarak = $geofenceHelper->haversineGreatCircleDistance($point->position_latitude, $point->position_longitude, $v->position_latitude, $v->position_longitude);
                    if($jarak<=18 && $v->spraying=='Y'){
                        $overlapped_area[] = $point->position_latitude.','.$point->position_longitude.'('.$jarak.' m) @ '.date('Y-m-d H:i:s', $point->timestamp);
                        $is_overlap = 1;
                    }
                }
                $rrk = new ReportRencanaKerja;
                $rrk->rencana_kerja_id = $rk->id;
                $rrk->tanggal = $rk->tgl;
                $rrk->shift = $rk->shift_nama;
                $rrk->lokasi = $rk->lokasi_kode;
                $rrk->luas_bruto = $rk->lokasi_lsbruto;
                $rrk->luas_netto = $rk->lokasi_lsnetto;
                $rrk->kode_aktivitas = $rk->aktivitas_kode;
                $rrk->nama_aktivitas = $rk->aktivitas_nama;
                $rrk->nozzle = $rk->nozzle_nama;
                $rrk->volume = $rk->volume;
                $rrk->kode_unit = $rk->unit_id;
                $rrk->nama_unit = $rk->unit_label;
                $rrk->device_id = $rk->unit_source_device_id;
                $rrk->operator = $rk->operator_nama;
                $rrk->driver = $rk->driver_nama;
                $rrk->kasie = $rk->kasie_nama;
                $rrk->status = $rk->status_nama;
                $rrk->jam_mulai = $rk->jam_mulai;
                $rrk->jam_selesai = $rk->jam_selesai;
                $rrk->latitude = $v->position_latitude;
                $rrk->longitude = $v->position_longitude;
                $rrk->position_direction = $v->position_direction;
                $rrk->gsm_signal_level = !empty($v->gsm_signal_level) ? $v->gsm_signal_level : null;
                $rrk->timestamp = date('Y-m-d H:i:s', $v->timestamp);
                $rrk->position_speed = $v->position_speed;
                $rrk->din = !empty($v->din) ? $v->din : null;
                $rrk->din_1 = $v->din_1;
                $rrk->din_2 = $v->din_2;
                $rrk->din_3 = $v->din_3;
                $rrk->ritase = $v->ritase;
                $rrk->overlapping = $is_overlap;
                $rrk->arm_height_right = !empty($v->arm_height_right)?$v->arm_height_right:0;
                $rrk->arm_height_left = !empty($v->arm_height_left)?$v->arm_height_left:0;
                $rrk->temperature_right = !empty($v->temperature_right)?$v->temperature_right:0;
                $rrk->temperature_left = !empty($v->temperature_left)?$v->temperature_left:0;
                $rrk->save();
            }

            $rk->jam_laporan = date('Y-m-d H:i:s');   
            $rk->save();     
        }
        ini_set( 'memory_limit', $oldLimit );
    } 

    public function generate_rencana_kerja_summary(){
        set_time_limit(0);
        $geofenceHelper = new GeofenceHelper;
        $list_rk = RencanaKerja::whereRaw("status_id = 4 AND (jam_laporan IS NULL OR jam_laporan = '')")
            ->orderBy('id', 'ASC')
            ->get();
        foreach($list_rk AS $rk) {
            Log::info('START_GENERATING_SUMMARY #'.$rk->id);
            $aktivitas = Aktivitas::find($rk->aktivitas_id);
            $list_rs = ReportStatus::get();
            $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);
            if($rk->tgl>='2022-03-15') {
                $list = Lacak2::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
            } else {   
                $list = Lacak::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
            }
            $is_started = false;
            $waktu_berhenti = 0;
            $ritase = 1;
            $list_movement = [];
            foreach($list AS $k=>$v){
                $lokasi         = count($list_polygon) > 0 ? $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude) : $rk->lokasi_kode;
                $waktu_tempuh   = ($k==0) ? 0 : round(abs($v->timestamp - $list[$k-1]->timestamp),2);
                $nozzle_kanan   = $v->ain_1 != null ? $v->ain_1 : 0;
                $nozzle_kiri    = $v->ain_2 != null ? $v->ain_2 : 0;
                $width          = ($nozzle_kanan > 12.63 ? 18 : 0) + ($nozzle_kiri > 12.63 ? 18 : 0);
                $lebar_kanan    = ($nozzle_kanan > 12.63 ? 18 : 0);
                $lebar_kiri     = ($nozzle_kiri > 12.63 ? 18 : 0);
                $width          = ($nozzle_kanan > 12.63 ? 18 : 0) + ($nozzle_kiri > 12.63 ? 18 : 0);
                $jarak_tempuh   = ($k==0) ? 0 : round(abs($v->vehicle_mileage - $list[$k-1]->vehicle_mileage),3);
                $jarak_spray_kanan     = ($k==0) ? 0 : ($list[$k-1]->ain_1 > 12.63 ? $jarak_tempuh : 0);
                $jarak_spray_kiri     = ($k==0) ? 0 : ($list[$k-1]->ain_2 > 12.63 ? $jarak_tempuh : 0);
                if(!empty($lokasi) && $width >= 18) {
                    $is_started = true;
                    $obj = (object) [
                        'timestamp'                 => $v->timestamp,
                        'lokasi'                    => $lokasi,
                        'position_latitude'         => $v->position_latitude,
                        'position_longitude'        => $v->position_longitude,
                        'vehicle_mileage'           => $v->vehicle_mileage,
                        'nozzle_kanan'              => $nozzle_kanan,
                        'nozzle_kiri'               => $nozzle_kiri,
                        'width'                     => $width,
                        'jarak_spray_kanan'         => $jarak_spray_kanan,
                        'jarak_spray_kiri'          => $jarak_spray_kiri,
                    ];
                    if(array_key_exists($ritase, $list_movement)){
                        $list_movement[$ritase]['list_gps'][] = $obj;
                        $list_movement[$ritase]['jarak_spray_kanan'] += $jarak_spray_kanan;
                        $list_movement[$ritase]['jarak_spray_kiri'] += $jarak_spray_kiri;
                    } else {
                        $list_movement[$ritase] = [
                            'list_gps'          => [$obj],
                            'jarak_tempuh'      => 0,
                            'jam_mulai'         => 0,
                            'jam_selesai'       => 0,
                            'waktu_tempuh'      => 0,
                            'kecepatan'         => 0,
                            'jarak_spray_kanan' => $jarak_spray_kanan,
                            'jarak_spray_kiri'  => $jarak_spray_kiri
                        ];
                    }
                    $waktu_berhenti = 0;
                } else {
                    $waktu_berhenti += $waktu_tempuh;
                }
                if($is_started && $waktu_berhenti>=240){
                    $ritase += 1;
                    $is_started = false;
                }
                $rrk = new ReportRencanaKerja;
                $rrk->rencana_kerja_id = $rk->id;
                $rrk->tanggal = $rk->tgl;
                $rrk->shift = $rk->shift_nama;
                $rrk->lokasi = $rk->lokasi_kode;
                $rrk->luas_bruto = $rk->lokasi_lsbruto;
                $rrk->luas_netto = $rk->lokasi_lsnetto;
                $rrk->kode_aktivitas = $rk->aktivitas_kode;
                $rrk->nama_aktivitas = $rk->aktivitas_nama;
                $rrk->nozzle = $rk->nozzle_nama;
                $rrk->volume = $rk->volume;
                $rrk->kode_unit = $rk->unit_id;
                $rrk->nama_unit = $rk->unit_label;
                $rrk->device_id = $rk->unit_source_device_id;
                $rrk->operator = $rk->operator_nama;
                $rrk->driver = $rk->driver_nama;
                $rrk->kasie = $rk->kasie_nama;
                $rrk->status = $rk->status_nama;
                $rrk->jam_mulai = $rk->jam_mulai;
                $rrk->jam_selesai = $rk->jam_selesai;
                $rrk->latitude = $v->position_latitude;
                $rrk->longitude = $v->position_longitude;
                $rrk->position_direction = $v->position_direction;
                $rrk->gsm_signal_level = $v->gsm_signal_level;
                $rrk->timestamp = date('Y-m-d H:i:s', $v->timestamp);
                $rrk->position_speed = $v->position_speed;
                $rrk->din = $v->din;
                $rrk->din_1 = $v->din_1;
                $rrk->din_2 = $v->din_2;
                $rrk->din_3 = $v->din_3;
                $rrk->ritase = $ritase;
                $rrk->overlapping = null;
                $rrk->save();
            }
            $jarak_tempuh_total   = 0;
            $waktu_tempuh_total   = 0;
            $kecepatan_total      = 0;
            $jarak_spray_kanan_total   = 0;
            $jarak_spray_kiri_total   = 0;
            foreach($list_movement as $k=>$v){
                $list_gps = $v['list_gps'];
                if(count($list_gps)>0){
                    $mileage1       = $list_gps[0]->vehicle_mileage;
                    $mileage2       = count($list_gps) > 1 ? $list_gps[count($list_gps)-1]->vehicle_mileage : $mileage1;
                    $timestamp1     = $list_gps[0]->timestamp;
                    $timestamp2     = count($list_gps) > 1 ? $list_gps[count($list_gps)-1]->timestamp : $timestamp1;
                    $jarak_tempuh   = round(abs($mileage2 - $mileage1),3);
                    $waktu_tempuh   = round(abs($timestamp2 - $timestamp1),2);
                    $kecepatan      = $waktu_tempuh > 0 ? round($jarak_tempuh / ($waktu_tempuh/3600),2) : 0;
                    $list_movement[$k]['jarak_tempuh']  = $jarak_tempuh;
                    $list_movement[$k]['jam_mulai']     = $timestamp1;
                    $list_movement[$k]['jam_selesai']   = $timestamp2;
                    $list_movement[$k]['waktu_tempuh']  = $waktu_tempuh;
                    $list_movement[$k]['kecepatan']     = $kecepatan;
                    $jarak_tempuh_total += $jarak_tempuh;
                    $waktu_tempuh_total += $waktu_tempuh;
                }
                $stop_time = $k > 1 ? $list_movement[$k]['jam_mulai'] - $list_movement[$k-1]['jam_selesai'] : 0;
                $jarak_spray_kanan_total += $v['jarak_spray_kanan']; 
                $jarak_spray_kiri_total += $v['jarak_spray_kiri']; 
                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 1, $kecepatan);

                $luas_spray_total = ($v['jarak_spray_kanan'] * 1000 * 18 + $v['jarak_spray_kiri'] * 1000 * 18)/10000;
                $luas_standard_spray = 8000 / $rk->volume - 0.012 * (8000 / $rk->volume);
                $overlapping = ($luas_spray_total / $luas_standard_spray - 1)* 100;
                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 2, $overlapping);

                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 3, round($waktu_tempuh/60,2));

                $ketepatan_dosis = 100 - $overlapping;
                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 4, $ketepatan_dosis);

                $golden_time = date('H:i:s', $list_movement[$k]['jam_mulai']);
                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 5, $golden_time);

                $wing_level = 1.3;
                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 6, $wing_level);

                $this->saveRKS($rk->id, $k, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 999, 0);
            } 
            $jam_mulai          = count($list_movement) > 0 ? $list_movement[1]['jam_mulai'] : 0;
            $jam_selesai        = count($list_movement) > 1 ? $list_movement[count($list_movement)]['jam_selesai'] : $jam_mulai;
            $kecepatan_total    = $waktu_tempuh_total > 0 ? round($jarak_tempuh_total / ($waktu_tempuh_total/3600),2) : 0; 
            $luas_spray_total = ($jarak_spray_kanan_total * 1000 * 18 + $jarak_spray_kiri_total * 1000 * 18)/10000;

            $area_not_spray = 0;
            $this->saveRKS($rk->id, 999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 7, $area_not_spray);
            $this->saveRKS($rk->id, 999999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 999999, 0);
            $rk->jam_laporan = date('Y-m-d H:i:s');
            $rk->save();
            // // Jarak tempuh: Dihitung mulai spray sd stop spray ( m)
            // //Luas aplikasi spray total: (Jarak tempuh x 1000) x (36/10.000)
            // // Area overlapping: 1 - ( luas peta lok/ luas aplikasi spray total)
            // // Ketepatan dosis spray(%) 100%  - prosen overlapping
            // // Satu ritase: Waktu, jarak dan lebar semprot per satu tangki boom sprayer ( 8000 liter)
            // // Waktu tunggu antar rit: Waktu yg dihasilkan saat tidak ada aktivitas spray dr rit sblmnya ke start spray rit berikutnya
            Log::info('FINISH_GENERATING_SUMMARY #'.$rk->id);
        }
    } 

    function saveRKS($rencana_kerja_id, $ritase, $grup_aktivitas_id, $aktivitas_id, $nozzle_id, $volume_id, $parameter_id, $realisasi) {
        $nilai_standard = '';
        $bobot = 0;
        $nilai = 0;
        $nilai_bobot = 0;
        $parameter_nama = '';
        $kualitas = '';
        $list_rs = ReportStatus::get();
        if($parameter_id == 999999) {
            $list_rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
                ->whereRaw("(ritase = 999 OR parameter_id = 999)")
                ->get();
            foreach($list_rks as $rks){
                $nilai += $rks->nilai;
                $bobot += $rks->bobot;
                $nilai_bobot += $rks->nilai_bobot;
            }
            $nilai = $nilai / count($list_rks);
            $parameter_nama = 'Total Nilai Kualitas Spraying';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
            $rk1 = RencanaKerja::find($rencana_kerja_id);
            $rk1->kualitas = $kualitas;
            $rk1->jam_laporan = date('Y-m-d H:i:s');
            $rk1->save();
        } else if($parameter_id == 999) {
            $list_rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
                ->where('ritase', $ritase)
                ->where('parameter_id', '<', 999)
                ->get();
            foreach($list_rks as $rks){
                $bobot += $rks->bobot;
                $nilai_bobot += $rks->nilai_bobot;
            }
            $nilai = round($nilai_bobot / $bobot,2) * 100;
            $parameter_nama = 'Total';
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
        } else {
            $rpb = ReportParameterBobot::where('grup_aktivitas_id', $grup_aktivitas_id)
                ->where('report_parameter_id', $parameter_id)
                ->first();
            $bobot = !empty($rpb->bobot) ? $rpb->bobot : 0;
            $std =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', $parameter_id)
                ->where('report_parameter_standard.aktivitas_id', $aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $nozzle_id)
                ->where('report_parameter_standard.volume_id', $volume_id)
                ->where('d.point', 100)
                ->first(['d.*']);
            $nilai_standard = $std != null ? $std->range_1.' - '.$std->range_2 : '';
            if($std != null) {
                if($std->range_1=='-999') {
                    $nilai_standard = '<= '.$std->range_2;
                } else if($std->range_2=='999') {
                    $nilai_standard = '>= '.$std->range_1;
                } else {
                    $nilai_standard = $std->range_1.' - '.$std->range_2;
                }
            }
            $list_rps =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', $parameter_id)
                ->where('report_parameter_standard.aktivitas_id', $aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $nozzle_id)
                ->where('report_parameter_standard.volume_id', $volume_id)
                ->orderByRaw("d.range_1*1 ASC")
                ->get(['d.*']);
            foreach($list_rps AS $rps){
                if($parameter_id==5){
                    $dt_realisasi = date('Y-m-d '.$realisasi);
                    if($rps->range_1 > $rps->range_2) {
                        $dt_range_1 = date('Y-m-d '.$rps->range_1,strtotime("-1 days"));
                    } else {
                        $dt_range_1 = date('Y-m-d '.$rps->range_1);
                    }
                    $dt_range_2 = date('Y-m-d '.$rps->range_2);
                    if($dt_range_1 <= $dt_realisasi && $realisasi <= doubleval($rps->range_2)){
                        $nilai = $rps->point;
                        break;
                    }
                } else {
                    if(doubleval($rps->range_1) <= $realisasi && $realisasi <= doubleval($rps->range_2)){
                        $nilai = $rps->point;
                        break;
                    }
                }
            }
            $nilai_bobot = $nilai / 100 * $bobot;
            $rp = ReportParameter::find($parameter_id);
            $parameter_nama = $rp->nama;
            foreach($list_rs as $v){
                if(doubleval($v->range_1) <= $nilai && $nilai <= doubleval($v->range_2)){
                    $kualitas = $v->status;
                    break;
                }
            }
        }
        $rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja_id)
            ->where('ritase', $ritase)
            ->where('parameter_id', $parameter_id)
            ->first();
        if($rks==null){
            $rks = new RencanaKerjaSummary;
            $rks->rk_id = $rencana_kerja_id;
            $rks->ritase = $ritase;
            $rks->parameter_id = $parameter_id;
            $rks->parameter_nama = $parameter_nama;
        }
        $rks->standard      = $nilai_standard;
        $rks->realisasi     = $realisasi;
        $rks->nilai         = $nilai;
        $rks->bobot         = $bobot;
        $rks->nilai_bobot   = $nilai_bobot;
        $rks->kualitas      = $kualitas;
        $rks->save();
    }

    protected function pull_data_lacak(){
        try {
            $last_data = Lacak::orderBy('created_at', 'DESC')->limit(1)->first();
            $last_created_at = $last_data == null ? '' : $last_data->created_at;
            Log::info('LACAK_START_SYNCING...');
            Log::info('LACAK_LAST_CREATED_AT : '.$last_created_at);
            $base_url = 'https://ggf-vectrk-jkt01.gg-foods.com';
            $client = new Client();
            $res = $client->request('GET', $base_url.'/api/lacak/sync_down?created_at='.$last_created_at.'&limit=5000', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTYzNzU0ODk1NywiZXhwIjoxNjQyNzMyOTU3LCJuYmYiOjE2Mzc1NDg5NTcsImp0aSI6IlI3RXR3Y05WWHdCWU10eDEiLCJzdWIiOjEsInBydiI6ImY2YjcxNTQ5ZGI4YzJjNDJiNzU4MjdhYTQ0ZjAyYjdlZTUyOWQyNGQifQ.qj6_uWB7BJlDC2_IONsZHecGo1AS-W4e7c4Zt0TE5TA",
                    'APP-VERSION' => '1.0.0'
                ]
            ]);
            $body = json_decode($res->getBody());
            foreach($body->data AS $k=>$v) {
                $lacak = Lacak::find($v->id);
                if($lacak==null){
                    $lacak = new Lacak();
                    $lacak->id = $v->id;
                }
                $lacak->ain_1  = isset($v->ain_1) ? $v->ain_1 : null;
                $lacak->ain_2  = isset($v->ain_2) ? $v->ain_2 : null;
                $lacak->battery_charging_status  = isset($v->battery_charging_status) ? $v->battery_charging_status : null;
                $lacak->battery_current  = isset($v->battery_current) ? $v->battery_current : null;
                $lacak->battery_temperature  = isset($v->battery_temperature) ? $v->battery_temperature : null;
                $lacak->battery_voltage  = isset($v->battery_voltage) ? $v->battery_voltage : null;
                $lacak->button_pressed_status  = isset($v->button_pressed_status) ? $v->button_pressed_status : null;
                $lacak->cable_connected_status  = isset($v->cable_connected_status) ? $v->cable_connected_status : null;
                $lacak->can_battery_voltage  = isset($v->can_battery_voltage) ? $v->can_battery_voltage : null;
                $lacak->can_car_closed_remote_status  = isset($v->can_car_closed_remote_status) ? $v->can_car_closed_remote_status : null;
                $lacak->can_car_closed_status  = isset($v->can_car_closed_status) ? $v->can_car_closed_status : null;
                $lacak->can_connection_state_1  = isset($v->can_connection_state_1) ? $v->can_connection_state_1 : null;
                $lacak->can_connection_state_2  = isset($v->can_connection_state_2) ? $v->can_connection_state_2 : null;
                $lacak->can_connection_state_3  = isset($v->can_connection_state_3) ? $v->can_connection_state_3 : null;
                $lacak->can_driver_door_status  = isset($v->can_driver_door_status) ? $v->can_driver_door_status : null;
                $lacak->can_dynamic_ignition_status  = isset($v->can_dynamic_ignition_status) ? $v->can_dynamic_ignition_status : null;
                $lacak->can_engine_ignition_status  = isset($v->can_engine_ignition_status) ? $v->can_engine_ignition_status : null;
                $lacak->can_engine_load_level  = isset($v->can_engine_load_level) ? $v->can_engine_load_level : null;
                $lacak->can_engine_motorhours  = isset($v->can_engine_motorhours) ? $v->can_engine_motorhours : null;
                $lacak->can_engine_rpm  = isset($v->can_engine_rpm) ? $v->can_engine_rpm : null;
                $lacak->can_engine_temperature  = isset($v->can_engine_temperature) ? $v->can_engine_temperature : null;
                $lacak->can_engine_working_status  = isset($v->can_engine_working_status) ? $v->can_engine_working_status : null;
                $lacak->can_fuel_consumed  = isset($v->can_fuel_consumed) ? $v->can_fuel_consumed : null;
                $lacak->can_fuel_level  = isset($v->can_fuel_level) ? $v->can_fuel_level : null;
                $lacak->can_fuel_volume  = isset($v->can_fuel_volume) ? $v->can_fuel_volume : null;
                $lacak->can_handbrake_status  = isset($v->can_handbrake_status) ? $v->can_handbrake_status : null;
                $lacak->can_hood_status  = isset($v->can_hood_status) ? $v->can_hood_status : null;
                $lacak->can_ignition_key_status  = isset($v->can_ignition_key_status) ? $v->can_ignition_key_status : null;
                $lacak->can_lvc_module_control_bitmask  = isset($v->can_lvc_module_control_bitmask) ? $v->can_lvc_module_control_bitmask : null;
                $lacak->can_module_id  = isset($v->can_module_id) ? $v->can_module_id : null;
                $lacak->can_module_sleep_mode  = isset($v->can_module_sleep_mode) ? $v->can_module_sleep_mode : null;
                $lacak->can_parking_status  = isset($v->can_parking_status) ? $v->can_parking_status : null;
                $lacak->can_passenger_door_status  = isset($v->can_passenger_door_status) ? $v->can_passenger_door_status : null;
                $lacak->can_pedal_brake_status  = isset($v->can_pedal_brake_status) ? $v->can_pedal_brake_status : null;
                $lacak->can_program_id  = isset($v->can_program_id) ? $v->can_program_id : null;
                $lacak->can_rear_left_door_status  = isset($v->can_rear_left_door_status) ? $v->can_rear_left_door_status : null;
                $lacak->can_rear_right_door_status  = isset($v->can_rear_right_door_status) ? $v->can_rear_right_door_status : null;
                $lacak->can_reverse_gear_status  = isset($v->can_reverse_gear_status) ? $v->can_reverse_gear_status : null;
                $lacak->can_throttle_pedal_level  = isset($v->can_throttle_pedal_level) ? $v->can_throttle_pedal_level : null;
                $lacak->can_tracker_counted_mileage  = isset($v->can_tracker_counted_mileage) ? $v->can_tracker_counted_mileage : null;
                $lacak->can_trunk_status  = isset($v->can_trunk_status) ? $v->can_trunk_status : null;
                $lacak->can_vehicle_battery_level  = isset($v->can_vehicle_battery_level) ? $v->can_vehicle_battery_level : null;
                $lacak->can_vehicle_mileage  = isset($v->can_vehicle_mileage) ? $v->can_vehicle_mileage : null;
                $lacak->can_vehicle_speed  = isset($v->can_vehicle_speed) ? $v->can_vehicle_speed : null;
                $lacak->can_webasto_status  = isset($v->can_webasto_status) ? $v->can_webasto_status : null;
                $lacak->device_id  = isset($v->device_id) ? $v->device_id : null;
                $lacak->din  = isset($v->din) ? $v->din : null;
                $lacak->din_1  = isset($v->din_1) ? $v->din_1 : null;
                $lacak->din_2  = isset($v->din_2) ? $v->din_2 : null;
                $lacak->din_3  = isset($v->din_3) ? $v->din_3 : null;
                $lacak->dout  = isset($v->dout) ? $v->dout : null;
                $lacak->dout_1  = isset($v->dout_1) ? $v->dout_1 : null;
                $lacak->dout_2  = isset($v->dout_2) ? $v->dout_2 : null;
                $lacak->engine_ignition_status  = isset($v->engine_ignition_status) ? $v->engine_ignition_status : null;
                $lacak->external_powersource_voltage  = isset($v->external_powersource_voltage) ? $v->external_powersource_voltage : null;
                $lacak->gnss_state_enum  = isset($v->gnss_state_enum) ? $v->gnss_state_enum : null;
                $lacak->gnss_status  = isset($v->gnss_status) ? $v->gnss_status : null;
                $lacak->gsm_cellid  = isset($v->gsm_cellid) ? $v->gsm_cellid : null;
                $lacak->gsm_lac  = isset($v->gsm_lac) ? $v->gsm_lac : null;
                $lacak->gsm_mnc  = isset($v->gsm_mnc) ? $v->gsm_mnc : null;
                $lacak->gsm_network_roaming_status  = isset($v->gsm_network_roaming_status) ? $v->gsm_network_roaming_status : null;
                $lacak->gsm_signal_level  = isset($v->gsm_signal_level) ? $v->gsm_signal_level : null;
                $lacak->ident  = isset($v->ident) ? $v->ident : null;
                $lacak->immobilizer_keys_status  = isset($v->immobilizer_keys_status) ? $v->immobilizer_keys_status : null;
                $lacak->immobilizer_service_status  = isset($v->immobilizer_service_status) ? $v->immobilizer_service_status : null;
                $lacak->movement_status  = isset($v->movement_status) ? $v->movement_status : null;
                $lacak->position_altitude  = isset($v->position_altitude) ? $v->position_altitude : null;
                $lacak->position_direction  = isset($v->position_direction) ? $v->position_direction : null;
                $lacak->position_hdop  = isset($v->position_hdop) ? $v->position_hdop : null;
                $lacak->position_latitude  = isset($v->position_latitude) ? $v->position_latitude : null;
                $lacak->position_longitude  = isset($v->position_longitude) ? $v->position_longitude : null;
                $lacak->position_pdop  = isset($v->position_pdop) ? $v->position_pdop : null;
                $lacak->position_satellites  = isset($v->position_satellites) ? $v->position_satellites : null;
                $lacak->position_speed  = isset($v->position_speed) ? $v->position_speed : null;
                $lacak->position_valid  = isset($v->position_valid) ? $v->position_valid : null;
                $lacak->segment_can_fuel_consumed  = isset($v->segment_can_fuel_consumed) ? $v->segment_can_fuel_consumed : null;
                $lacak->segment_can_vehicle_mileage  = isset($v->segment_can_vehicle_mileage) ? $v->segment_can_vehicle_mileage : null;
                $lacak->server_timestamp  = isset($v->server_timestamp) ? $v->server_timestamp : null;
                $lacak->timestamp  = isset($v->timestamp) ? $v->timestamp : null;
                $lacak->vehicle_mileage  = isset($v->vehicle_mileage) ? $v->vehicle_mileage : null;
                $lacak->created_at = isset($v->created_at) ? $v->created_at : null; 
                $lacak->save();
            }
            Log::info('LACAK_FINISH_SYNCING...');
        } catch (\Exception $e) {
            Log::error('LACAK_ERROR: '.$e->getMessage());
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}

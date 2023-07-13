<?php

namespace App\Http\Controllers;

use App\Model\RencanaKerja;
use App\Model\ReportRencanaKerja;
use App\Helper\GeofenceHelper;
use App\Model\SystemConfiguration;
use App\Model\Lacak;
use App\Model\Lacak2;
use Illuminate\Support\Facades\DB;

class RencanaKerjaController extends Controller
{
    public function generate_rencana_kerja_report()
    {
        try {
            $oldLimit = ini_get('memory_limit');
            ini_set('memory_limit', '-1');
            set_time_limit(0);
            $geofenceHelper = new GeofenceHelper;

            $list_rk = RencanaKerja::whereRaw("status_id = 4 AND jam_laporan IS NULL")
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get();

            foreach ($list_rk as $rk) {
                $reportExists = ReportRencanaKerja::where('rencana_kerja_id', $rk->id)->exists();

                if (!$reportExists) {
                    continue; // Skip processing if report doesn't exist for the current RencanaKerja
                }

                ReportRencanaKerja::where('rencana_kerja_id', $rk->id)->delete();
                $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);

                $sysconf = SystemConfiguration::where('code', 'OFFLINE_UNIT')->first(['value']);
                $offline_units = !empty($sysconf->value) ? explode(',', $sysconf->value) : [];
                if (in_array($rk->unit_source_device_id, $offline_units)) {
                    $table_name = 'lacak_'.$rk->unit_source_device_id;
                    $list = DB::table($table_name)
                        ->where('utc_timestamp', '>=', strtotime($rk->jam_mulai))
                        ->where('utc_timestamp', '<=', strtotime($rk->jam_selesai))
                        ->orderBy('utc_timestamp', 'ASC')
                        ->selectRaw("latitude AS position_latitude, longitude AS position_longitude, altitude AS position_altitude, bearing AS position_direction, speed AS position_speed, 0 AS ain_1, 0 AS ain_2, pump_switch_right AS din_1, pump_switch_left AS din_2, pump_switch_main AS din_3, '' AS payload_text, `utc_timestamp` AS timestamp, arm_height_right, arm_height_left, temperature_right, temperature_left")
                        ->get();
                } else {
                    if ($rk->tgl >= '2022-03-15') {
                        $list = Lacak2::where('ident', $rk->unit_source_device_id)
                            ->where('timestamp', '>=', strtotime($rk->jam_mulai))
                            ->where('timestamp', '<=', strtotime($rk->jam_selesai))
                            ->orderBy('timestamp', 'ASC')
                            ->get();
                    } else {
                        $list = Lacak::where('ident', $rk->unit_source_device_id)
                            ->where('timestamp', '>=', strtotime($rk->jam_mulai))
                            ->where('timestamp', '<=', strtotime($rk->jam_selesai))
                            ->orderBy('timestamp', 'ASC')
                            ->get();
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

            ini_set('memory_limit', $oldLimit);

            return "Rencana Kerja reports generated successfully!";
        } catch (\Exception $e) {
            return "Failed to generate Rencana Kerja reports: " . $e->getMessage();
        }
    }
}

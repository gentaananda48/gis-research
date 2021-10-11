<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\User;
use App\Center\GridCenter;
use App\Model\Tracker;
use App\Transformer\UserTransformer;
use Illuminate\Support\Facades\DB;
use App\Helper\GeofenceHelper;
use App\Model\KoordinatLokasi;
use App\Model\KoordinatLokasiTemp;
use App\Model\Lacak;
use App\Model\RencanaKerja;
use App\Model\ReportParameter;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['users','test']]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request){
        return view('home', []);
    }

    public function home(){
        return redirect('/');
    }

    public function users()
    {
        $param = $_GET;
        $query = User::select();
        $user_data = new GridCenter($query, $param);
    
        echo json_encode($user_data->render(new UserTransformer()));
        exit;
    }

    public function check_geofence(Request $request){
        $coordinate = explode(',', $request->coordinate);
        $list = KoordinatLokasi::orderBy('lokasi', 'ASC')
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_polygon = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        $geofenceHelper = new GeofenceHelper;
        $lokasi = $geofenceHelper->checkLocation($list_polygon, trim($coordinate[0]), trim($coordinate[1]));
        echo "LOKASI: ".$lokasi."<br/>";
    }

    public function test(){
        set_time_limit(0);
        // get Rencana Kerja
        $rk = RencanaKerja::find(1);
        echo $rk->lokasi_nama.$rk->unit_label.$rk->aktivitas_nama.$rk->nozzle_nama.$rk->volume.' '.$rk->unit_source_device_id."<br/>";

        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon('L', $rk->lokasi_kode);
        $list = Lacak::where('ident', $rk->unit_source_device_id)->where('timestamp', '>=', strtotime($rk->jam_mulai))->where('timestamp', '<=', strtotime($rk->jam_selesai))->orderBy('timestamp', 'ASC')->get();
        $is_started = false;
        $waktu_berhenti = 0;
        $ritase = 1;
        $list_movement = [];
        foreach($list AS $k=>$v){
            $lokasi         = $geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
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
            // echo date('Y-m-d H:i:s.Z', $v->timestamp).$lokasi."<br/>";
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
        }
        $jarak_tempuh_total   = 0;
        $waktu_tempuh_total   = 0;
        $kecepatan_total      = 0;
        $jarak_spray_kanan_total   = 0;
        $jarak_spray_kiri_total   = 0;
        foreach($list_movement as $k=>$v){
            $list_gps = $v['list_gps'];
            echo "RITASE ".$k."<br/>";
            foreach($list_gps as $v2){
                echo "[".date('Y-m-d H:i:s.Z', $v2->timestamp)."] Lokasi: ".$v2->lokasi.", Koordinat: [".$v2->position_latitude.",".$v2->position_longitude."], Mileage: ".$v2->vehicle_mileage.", Jarak Spray Kanan: ".$v2->jarak_spray_kanan.", Jarak Spray Kiri: ".$v2->jarak_spray_kiri.", WIDTH: ".$v2->width." <br/>";
            }
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
            echo "<hr/>";
            echo "Waktu Tunggu : ".round($stop_time/60,2)." Menit<br/>";
            echo "Jarak Spray : ".$jarak_tempuh." KM<br/>";
            echo "Waktu Spray :".round($waktu_tempuh/60,2)." Menit<br/>";
            echo "Kecepatan Operasi : ".$kecepatan." KM/Jam. ";
            $list_rp = ReportParameter::where('tipe', 'Kecepatan Operasi')
                ->where('kriteria_1', $rk->aktivitas_nama)
                ->where('kriteria_2', $rk->nozzle_nama)
                ->where('kriteria_3', $rk->volume)
                ->orderBy('range_1', 'ASC')
                ->get();
            $nilai_bobot_kecepatan = 0;
            foreach($list_rp AS $rp){
                if(doubleval($rp->range_1) <= $kecepatan && $kecepatan <= doubleval($rp->range_2)){
                    $nilai_bobot_kecepatan = $rp->point*30;
                }
            }
            echo "BOBOT : ".$nilai_bobot_kecepatan."<br/>";
            $luas_spray_total = ($v['jarak_spray_kanan'] * 1000 * 18 + $v['jarak_spray_kiri'] * 1000 * 18)/10000;
            echo "Luas Spray : ".$luas_spray_total." Ha <br/>";
            $luas_standard_spray = 8000 / $rk->volume - 0.012 * (8000 / $rk->volume);
            echo "Luas Standard Spray : ".$luas_standard_spray." Ha <br/>";
            $overlapping = ($luas_spray_total / $luas_standard_spray - 1)* 100;
            echo "Overlapping : ".$overlapping." %. ";
            $list_rp = ReportParameter::where('tipe', 'Overlapping')
                ->where('kriteria_1', $rk->aktivitas_nama)
                ->where('kriteria_2', $rk->nozzle_nama)
                ->orderBy('range_1', 'ASC')
                ->get();
            $nilai_bobot_overlapping = 0;
            foreach($list_rp AS $rp){
                if(doubleval($rp->range_1) <= $overlapping && $overlapping <= doubleval($rp->range_2)){
                    $nilai_bobot_overlapping = $rp->point*20;
                }
            }
            echo "BOBOT : ".$nilai_bobot_overlapping."<br/>";
            echo "Waktu Spray per Ritase :".round($waktu_tempuh/60,2)." Menit. ";
            $list_rp = ReportParameter::where('tipe', 'Waktu Spray')
                ->where('kriteria_1', $rk->aktivitas_nama)
                ->where('kriteria_2', $rk->nozzle_nama)
                ->orderBy('range_1', 'ASC')
                ->get();
            $nilai_bobot_waktu_spray = 0;
            foreach($list_rp AS $rp){
                if(doubleval($rp->range_1) <= $waktu_tempuh && $waktu_tempuh <= doubleval($rp->range_2)){
                    $nilai_bobot_waktu_spray = $rp->point*10;
                }
            }
            echo "BOBOT : ".$nilai_bobot_waktu_spray."<br/>";
            $ketepatan_dosis = 100 - $overlapping;
            echo "Ketepatan Dosis :".$ketepatan_dosis.". ";
            $list_rp = ReportParameter::where('tipe', 'Ketepatan Dosis')
                ->where('kriteria_1', $rk->aktivitas_nama)
                ->where('kriteria_2', $rk->nozzle_nama)
                ->orderBy('range_1', 'ASC')
                ->get();
            $nilai_bobot_ketepatan_dosis = 0;
            foreach($list_rp AS $rp){
                if(doubleval($rp->range_1) <= $ketepatan_dosis && $ketepatan_dosis <= doubleval($rp->range_2)){
                    $nilai_bobot_ketepatan_dosis = $rp->point*10;
                }
            }
            echo "BOBOT : ".$nilai_bobot_ketepatan_dosis."<br/>";

            $golden_time = date('H:i', $list_movement[$k]['jam_mulai']);
            echo "Golden Time :".$golden_time.". ";
            $list_rp = ReportParameter::where('tipe', 'Golden Time')
                ->where('kriteria_1', $rk->aktivitas_nama)
                ->where('kriteria_2', $rk->nozzle_nama)
                ->orderBy('range_1', 'ASC')
                ->get();
            $nilai_bobot_golden_time = 0;
            foreach($list_rp AS $rp){
                if($rp->range_1 >= $golden_time){
                    $nilai_bobot_golden_time = $rp->point*15;
                }
            }
            echo "BOBOT : ".$nilai_bobot_golden_time."<br/>";
            echo "<hr/>";
        } 
        exit;
        $jam_mulai          = count($list_movement) > 0 ? $list_movement[1]['jam_mulai'] : 0;
        $jam_selesai        = count($list_movement) > 1 ? $list_movement[count($list_movement)]['jam_selesai'] : $jam_mulai;
        $kecepatan_total    = round($jarak_tempuh_total / ($waktu_tempuh_total/3600),2); 
        echo "<br/>";
        // echo "JARAK TEMPUH TOTAL : ".$jarak_tempuh_total." KM<br/>";
        // echo "JAM MULAI :".date('Y-m-d H:i:s.Z', $jam_mulai)."<br/>";
        // echo "JAM SELESAI :".date('Y-m-d H:i:s.Z', $jam_selesai)."<br/>";
        // echo "WAKTU TEMPUH :".$waktu_tempuh_total." Detik<br/>";
        // echo "KECEPATAN : ".$kecepatan_total." KM/Jam<br/>";
        echo "JARAK SPRAY KANAN : ".$jarak_spray_kanan_total." KM<br/>";
        echo "JARAK SPRAY KIRI : ".$jarak_spray_kiri_total." KM<br/>";
        $luas_spray_total = ($jarak_spray_kanan_total * 1000 * 18 + $jarak_spray_kiri_total * 1000 * 18)/10000;
        echo "LUAS SPRAY : ".$luas_spray_total." Ha<br/>";

        $bobot_kecepatan = 0;
        if($kecepatan_total > 6.8){
            $bobot_kecepatan = 50;
        } else if($kecepatan_total < 5.6){
            $bobot_kecepatan = 50;
        } else {
            $bobot_kecepatan = 100;
        }
        $bobot_kecepatan = $bobot_kecepatan / 100 * 30;  
        $golden_time = 0; 
        if(date('H', $jam_mulai) >= '16' || date('H', $jam_mulai) <= '11'){
            $golden_time = 100;
        } else {
            $golden_time = 50;
        }
        $bobot_golden_time = $golden_time / 100 * 15; 
        $data = [
            'jam_mulai'             => $jam_mulai,
            'jam_selesai'           => $jam_selesai,
            'jarak_tempuh'          => $jarak_tempuh_total,
            'waktu_tempuh'          => ($waktu_tempuh_total/60),
            'kecepatan'             => $kecepatan_total,
            'bobot_keceparan'       => $bobot_kecepatan,
            'golden_time'           => $golden_time,
            'bobot_golden_time'     => $bobot_golden_time,
            'list_ritase'           => $list_movement
        ];
        //echo json_encode($data);
        //print_r($data);
        exit;
    }

}

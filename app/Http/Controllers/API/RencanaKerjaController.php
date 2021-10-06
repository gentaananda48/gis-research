<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaLog;
use App\Model\RencanaKerjaSummary;
use App\Model\Shift;
use App\Model\Lokasi;
use App\Model\Aktivitas;
use App\Model\AktivitasParameter;
use App\Model\Unit;
use App\Model\User;
use App\Model\Status;
use App\Model\Tracker;
use App\Model\KoordinatLokasi;
use App\Helper\GeofenceHelper;
use App\Model\Lacak;
use App\Model\TindakLanjutPending;
use App\Model\AlasanPending;
use App\Model\Parameter;

class RencanaKerjaController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        $list =RencanaKerja::orderBy('tgl', 'DESC')->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list2(Request $request){
    	$list_status = explode(',', $request->status);
        $list =RencanaKerja::where('.tgl', $request->tgl)
            ->whereIn('status_id', $list_status)
            ->orderBy('unit_label', 'ASC')
            ->orderBy('status_urutan', 'DESC')
            ->get();
        $list3 = [];
        foreach($list as $v){
        	if(array_key_exists($v->unit_label, $list3)){
        		$list3[$v->unit_label]['list'][] = $v;
        	} else {
        		$list3[$v->unit_label] = ['unit_label' => $v->unit_label, 'list' => [$v]];
        	}
        }
        $list3 = array_values($list3);
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list3
        ]);
    }

    public function list3(Request $request){
        $list =RencanaKerja::where('tgl', $request->tgl)
            ->where('operator_id', $request->operator_id)
            ->orderBy('shift_id', 'ASC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function detail(Request $request){
    	$rk = RencanaKerja::find($request->id);
		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> $rk
    	]);
	} 

	public function monitor(Request $request){
    	$rk = RencanaKerja::find($request->id);
    	$ap = AktivitasParameter::where('aktivitas_id', $rk->aktivitas_id)->where('parameter_id', 1)->first();
    	$rk->standard_kecepatan = $ap->standard;
		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> $rk
    	]);
	} 

	public function unit(Request $request){
		$rk = RencanaKerja::find($request->id);
        $unit = Unit::find($rk->unit_id);
        if($unit==null){
            return response()->json([
                'status'    => false, 
                'message'   => 'Unit ID: '.$request->id.' not found', 
                'data'      => null
            ]);
        }

        $lacak = Lacak::where('ident', $unit->source_device_id)->orderBy('timestamp', 'DESC')->limit(1)->first();
        $unit->position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
        $unit->position_longitude       = $lacak != null ? $lacak->position_longitude : 0;
        $unit->movement_status          = $lacak != null ? $lacak->movement_status : 0;
        $unit->movement_status_desc     = !empty($unit->movement_status) ? 'moving': 'stopped';
        $unit->gsm_signal_level         = $lacak != null ? $lacak->gsm_signal_level : 0;
        $unit->position_altitude        = $lacak != null ? $lacak->position_altitude : 0;
        $unit->position_direction       = $lacak != null ? $lacak->position_direction : 0;
        $unit->position_speed           = $lacak != null ? $lacak->position_speed : 0;
        $unit->nozzle_kanan             = $lacak != null && $lacak->ain_1 != null ? $lacak->ain_1 : 0;
        $unit->nozzle_kiri              = $lacak != null && $lacak->ain_2 != null ? $lacak->ain_2 : 0;

        $geofenceHelper = new GeofenceHelper;
        $list_polygon = $geofenceHelper->createListPolygon();
        $unit->lokasi = $geofenceHelper->checkLocation($list_polygon, $unit->position_latitude, $unit->position_longitude);
        $unit->lokasi = !empty($unit->lokasi) ? substr($unit->lokasi,0,strlen($unit->lokasi)-2) : '';
        return response()->json([
            'status'    => true, 
            'message'   => '', 
            'data'      => $unit
        ]);
    }

	public function get_master_data(Request $request){
		$list_shift 	= Shift::orderBy('id', 'ASC')->get(['id', 'nama']);
		$list_lokasi 	= Lokasi::orderBy('kode', 'ASC')->get(['id', 'kode', 'nama', 'lsbruto', 'lsnetto']);
		$list_aktivitas = Aktivitas::orderBy('kode', 'ASC')->get(['id', 'kode', 'nama']);
		$list_unit 		= Unit::orderBy('label', 'ASC')->get(['id', 'label']);
		$list_operator 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_SPRAY_OPERATOR')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
		$list_driver 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_SPRAY_DRIVER')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
		$data = [
			'list_shift'		=> $list_shift,
			'list_lokasi'		=> $list_lokasi,
			'list_aktivitas'	=> $list_aktivitas,
			'list_unit'			=> $list_unit,
			'list_operator' 	=> $list_operator,
			'list_driver' 		=> $list_driver
		];
		return response()->json([
        	'status' 	=> true, 
        	'message' 	=> 'Submitted successfully', 
        	'data' 		=> $data
      	]);
	}

    // Create
  	public function create(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = new RencanaKerja;
	      	$rk->tgl 			= $request->tgl;
	      	$rk->shift_id 		= $request->shift_id;
	      	$shift 				= Shift::find($request->shift_id);
	      	$rk->shift_nama 	= $shift->nama;
	      	$rk->lokasi_id 		= $request->lokasi_id;
	      	$lokasi 		 	= Lokasi::find($request->lokasi_id);
	      	$rk->lokasi_kode 	= $lokasi->kode;
	      	$rk->lokasi_nama 	= $lokasi->nama;
	      	$rk->lokasi_lsbruto = $request->lokasi_lsbruto;
	      	$rk->lokasi_lsnetto = $request->lokasi_lsnetto;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$aktivitas 		 	= Aktivitas::find($request->aktivitas_id);
	      	$rk->aktivitas_kode = $aktivitas->kode;
	      	$rk->aktivitas_nama = $aktivitas->nama;
	      	$rk->unit_id  				= $request->unit_id;
	      	$unit 		 				= Unit::find($request->unit_id);
	      	$rk->unit_label				= $unit->label;
	      	$rk->unit_source_device_id 	= $unit->source_device_id;
	      	$rk->operator_id 			= $request->operator_id;
	      	$operator 		 			= User::find($request->operator_id);
	      	$rk->operator_nama 			= $operator->name;
	      	$rk->driver_id 				= $request->driver_id;
	      	$driver 		 			= User::find($request->driver_id);
	      	$rk->driver_nama 			= $driver->name;
	      	$rk->kasie_id  				= $user->id;
	      	$rk->kasie_nama 			= $user->name;
	      	$rk->status_id 				= 1;
	      	$status 		 			= Status::find(1);
	      	$rk->status_nama 			= $status->nama;
	      	$rk->status_urutan 			= $status->urutan;
	      	$rk->status_color 			= $status->color;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Create';
	      	$rkl->catatan 			= '';
	      	$rkl->status_id_lama 	= 0;
	      	$rkl->status_nama_lama 	= '';
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	// Update
  	public function update(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 	= $rk->status_id;
	      	$status_nama_lama 	= $rk->status_nama;
	      	$rk->tgl 			= $request->tgl;
	      	$rk->shift_id 		= $request->shift_id;
	      	$shift 				= Shift::find($request->shift_id);
	      	$rk->shift_nama 	= $shift->nama;
	      	$rk->lokasi_id 		= $request->lokasi_id;
	      	$lokasi 		 	= Lokasi::find($request->lokasi_id);
	      	$rk->lokasi_kode 	= $lokasi->kode;
	      	$rk->lokasi_nama 	= $lokasi->nama;
	      	$rk->lokasi_lsbruto = $request->lokasi_lsbruto;
	      	$rk->lokasi_lsnetto = $request->lokasi_lsnetto;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$aktivitas 		 	= Aktivitas::find($request->aktivitas_id);
	      	$rk->aktivitas_kode = $aktivitas->kode;
	      	$rk->aktivitas_nama = $aktivitas->nama;
	      	$rk->unit_id  				= $request->unit_id;
	      	$unit 		 				= Unit::find($request->unit_id);
	      	$rk->unit_label				= $unit->label;
	      	$rk->unit_source_device_id 	= $unit->source_device_id;
	      	$rk->operator_id 			= $request->operator_id;
	      	$operator 		 			= User::find($request->operator_id);
	      	$rk->operator_nama 			= $operator->name;
	      	$rk->driver_id 				= $request->driver_id;
	      	$driver 		 			= User::find($request->driver_id);
	      	$rk->driver_nama 			= $driver->name;
	      	$rk->kasie_id  				= $user->id;
	      	$rk->kasie_nama 			= $user->name;
	      	$rk->status_id 				= $request->status_id;
	      	$status 		 			= Status::find($rk->status_id);
	      	$rk->status_nama 			= $status->nama;
	      	$rk->status_urutan 			= $status->urutan;
	      	$rk->status_color 			= $status->color;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Update';
	      	$rkl->catatan 			= $request->catatan;
	      	$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	// Start Spraying
  	public function start_spraying(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 	= $rk->status_id;
	      	$status_nama_lama 	= $rk->status_nama;
	      	$rk->status_id 		= 2;
	      	$status 	 		= Status::find($rk->status_id);
	      	$rk->status_nama 	= $status->nama;
	      	$rk->status_urutan  = $status->urutan;
	      	$rk->status_color  	= $status->color;
	      	$rk->jam_mulai 	= date('Y-m-d H:i:s');
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Start Spraying';
	      	$rkl->catatan 			= '';
	      	$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	// Pending Spraying
  	public function pending_spraying(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 				= $rk->status_id;
	      	$status_nama_lama 	= $rk->status_nama;
	      	$rk->status_id 					= 3;
	      	$status 	 					= Status::find($rk->status_id);
	      	$rk->status_nama 				= $status->nama;
	      	$rk->status_urutan  			= $status->urutan;
	      	$rk->status_color  				= $status->color;
	      	$rk->jam_pending 				= date('Y-m-d H:i:s');
	      	$rk->pending_alasan_id 			= $request->alasan_pending_id;
	      	$alasan_pending 				= AlasanPending::find($rk->pending_alasan_id);
	      	$rk->pending_alasan_nama 		= $alasan_pending->nama;
	      	$rk->pending_tindak_lanjut_id 	= $request->tindak_lanjut_pending_id;
	      	$tindak_lanjut_pending 		 	= TindakLanjutPending::find($rk->pending_tindak_lanjut_id);
	      	$rk->pending_tindak_lanjut_nama = $tindak_lanjut_pending->nama;
	      	$rk->pending_catatan 			= $request->catatan;
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Pending Spraying';
	      	$rkl->catatan 			= $request->catatan;
	      	$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	public function summary(Request $request){
    	$rk = RencanaKerja::find($request->id);
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
            if(!empty($lokasi) && $v->position_speed>=5 && $width >= 18) {
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
        }
        $jam_mulai          = count($list_movement) > 0 ? $list_movement[1]['jam_mulai'] : 0;
        $jam_selesai        = count($list_movement) > 1 ? $list_movement[count($list_movement)]['jam_selesai'] : $jam_mulai;
        $kecepatan_total    = round($jarak_tempuh_total / ($waktu_tempuh_total/3600),2); 
        $bobot_kecepatan = 0;
        if($kecepatan_total > 6.8){
            $bobot_kecepatan = 50;
            $status_kecepatan = 'BURUK';
        } else if($kecepatan_total < 5.6){
            $bobot_kecepatan = 50;
            $status_kecepatan = 'BURUK';
        } else {
            $bobot_kecepatan = 100;
            $status_kecepatan = 'BAIK';
        }
        $bobot_kecepatan = $bobot_kecepatan / 100 * 30;  
        $this->saveRKS($rk->id, 1, $kecepatan_total, $bobot_kecepatan, $status_kecepatan);

        $luas_spray_total = ($jarak_spray_kanan_total * 1000 * 18 + $jarak_spray_kiri_total * 1000 * 18)/10000;
        $luas_lahan = $rk->lokasi_lsnetto;
        $overlapping = round(($luas_spray_total / $luas_lahan) - 1,2);
        $bobot_overlapping = 0;
        $status_overlapping = '';
        if($overlapping <= 0.55){
            $bobot_overlapping = 100;
            $status_overlapping = 'BAIK';
        } else if($overlapping >= 0.56 && $overlapping <= 2.09){
            $bobot_overlapping = 90;
            $status_overlapping = 'BAIK';
        } else if($overlapping >= 2.10 && $overlapping <= 3.59){
            $bobot_overlapping = 80;
            $status_overlapping = 'BAIK';
        } else if($overlapping >= 3.60 && $overlapping <= 5.50){
            $bobot_overlapping = 70;
            $status_overlapping = 'BURUK';
        } else {
            $bobot_overlapping = 50;
            $status_overlapping = 'BURUK';
        }
        $this->saveRKS($rk->id, 2, $overlapping, $bobot_overlapping, $status_overlapping);

        $ketepatan_dosis = 100  - ($overlapping / $luas_spray_total * 100);
        $bobot_ketepatan_dosis = 0;
        $status_ketepatan_dosis = '';
        if($ketepatan_dosis >= 89.96){
            $bobot_ketepatan_dosis = 100;
            $status_ketepatan_dosis = 'BAIK';
        } else if($ketepatan_dosis >= 80 && $ketepatan_dosis <= 89.95){
            $bobot_ketepatan_dosis = 90;
            $status_ketepatan_dosis = 'BAIK';
        } else if($ketepatan_dosis >= 70 && $ketepatan_dosis <= 79.99){
            $bobot_ketepatan_dosisg = 80;
            $status_ketepatan_dosis = 'BAIK';
        } else {
            $bobot_ketepatan_dosis = 50;
            $status_ketepatan_dosis = 'BURUK';
        }

        $this->saveRKS($rk->id, 4, $ketepatan_dosis, $bobot_ketepatan_dosis, $status_ketepatan_dosis);

        $golden_time = 0; 
        $status_golden_time = '';
        if(date('H', $jam_mulai) >= '16' || date('H', $jam_mulai) <= '11'){
            $golden_time = 100;
            $status_golden_time = 'BAIK';
        } else {
            $golden_time = 50;
            $status_golden_time = 'BURUK';
        }
        $bobot_golden_time = $golden_time / 100 * 15; 
        $this->saveRKS($rk->id, 5, $golden_time, $bobot_golden_time, $status_golden_time);

        $area_not_spray = $luas_lahan - $luas_spray_total;
        $bobot_area_not_spray = 5;
        $status_area_not_spray = '';
        $this->saveRKS($rk->id, 7, $area_not_spray, $bobot_area_not_spray, $status_area_not_spray);

        $wing_level = 1.3;
        $bobot_wing_level = 20;
        $status_wing_level = 'BAIK';
        $this->saveRKS($rk->id, 6, $wing_level, $bobot_wing_level, $status_wing_level);
        
        $waktu_tunggu_ritase = 0;
        $bobot_waktu_tunggu_ritase = 0;
        $status_waktu_tunggu_ritase = '';
        $this->saveRKS($rk->id, 8, $waktu_tunggu_ritase, $bobot_waktu_tunggu_ritase, $status_waktu_tunggu_ritase);
        
        $waktu_transport = 0;
        $bobot_waktu_transport = 0;
        $status_waktu_transport = '';
        $this->saveRKS($rk->id, 9, $waktu_transport, $bobot_waktu_transport, $status_waktu_transport);

        $waktu_spray_per_ritase = 0;
        $bobot_waktu_spray_per_ritase = 0;
        $status_waktu_spray_per_ritase = '';
        $this->saveRKS($rk->id, 3, $waktu_spray_per_ritase, $bobot_waktu_spray_per_ritase, $status_waktu_spray_per_ritase);

        $total_bobot = $bobot_kecepatan + $bobot_overlapping + $bobot_ketepatan_dosis + $bobot_golden_time + $bobot_area_not_spray + $bobot_wing_level + $bobot_waktu_spray_per_ritase;
        $status = '';
        if($total_bobot>=91){
        	$status = 'Excellent';
        } else if($total_bobot>=85 && $total_bobot>=90.99){
        	$status = 'Very Good';
        } else if($total_bobot>=80 && $total_bobot>=84.99){
        	$status = 'Good';
        } else {
        	$status = 'Poor';
        }
        $summary = [
            'luas_spray_total'				=> $luas_spray_total,
            'kecepatan'             		=> $kecepatan_total, 
            'status_kecepatan'				=> $status_kecepatan,
            'overlapping'					=> $overlapping,
            'status_overlapping'			=> $status_overlapping,
            'ketepatan_dosis'				=> round($ketepatan_dosis,2),
            'status_ketepatan_dosis'		=> $status_ketepatan_dosis,
            'golden_time'           		=> $golden_time,
            'status_golden_time'			=> $status_golden_time,
            'area_not_spray'				=> $area_not_spray,
            'status_area_not_spray'			=> $status_area_not_spray,
            'wing_level'					=> $wing_level,
            'status_wing_level'				=> $status_wing_level,
            'waktu_tunggu_ritase'			=> $waktu_tunggu_ritase,
            'status_waktu_tunggu_ritase'	=> $status_waktu_tunggu_ritase,
            'waktu_transport'				=> $waktu_transport,
            'status_waktu_transport'		=> $status_waktu_transport,
            'waktu_spray_per_ritase'		=> $waktu_spray_per_ritase,
            'status_waktu_spray_per_ritase'	=> $status_waktu_spray_per_ritase,
            'status'						=> $status
        ];

        // // Jarak tempuh: Dihitung mulai spray sd stop spray ( m)
        // //Luas aplikasi spray total: (Jarak tempuh x 1000) x (36/10.000)
        // // Area overlapping: 1 - ( luas peta lok/ luas aplikasi spray total)
        // // Ketepatan dosis spray(%) 100%  - prosen overlapping
        // // Satu ritase: Waktu, jarak dan lebar semprot per satu tangki boom sprayer ( 8000 liter)
        // // Waktu tunggu antar rit: Waktu yg dihasilkan saat tidak ada aktivitas spray dr rit sblmnya ke start spray rit berikutnya

		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> [
				'rk' 		=> $rk,
				'summary'	=> $summary
			]
    	]);
	} 

	function saveRKS($rk_id, $parameter_id, $nilai, $bobot, $kualitas) {
		$rks = RencanaKerjaSummary::where('rk_id', $rk_id)->where('parameter_id', $parameter_id)->first();
        if($rks == null){
        	$rks = new RencanaKerjaSummary();
        	$rks->rk_id 		= $rk_id;
        	$rks->parameter_id 	= $parameter_id;
        }
        $parameter = Parameter::find($parameter_id);
        $rks->parameter_nama 	= $parameter->nama;
        $rks->nilai 			= $nilai;
        $rks->bobot 			= $bobot;
        $rks->kualitas 			= $kualitas;
        $rks->save();
	}

	// Finish Spraying
  	public function finish_spraying(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 	= $rk->status_id;
	      	$status_nama_lama 	= $rk->status_nama;
	      	$rk->status_id 		= 2;
	      	$status 	 		= Status::find($rk->status_id);
	      	$rk->status_nama 	= $status->nama;
	      	$rk->status_urutan  = $status->urutan;
	      	$rk->status_color  	= $status->color;
	      	$rk->jam_selesai 	= date('Y-m-d H:i:s');
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Finish Spraying';
	      	$rkl->catatan 			= '';
	      	$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

	// Report Spraying
  	public function report_spraying(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 	= $rk->status_id;
	      	$rk->status_id 		= 4;
	      	$status 	 		= Status::find($rk->status_id);
	      	$rk->status_nama 	= $status->nama;
	      	$rk->status_urutan  = $status->urutan;
	      	$rk->status_color  	= $status->color;
	      	$rk->jam_laporan 	= date('Y-m-d H:i:s');
	      	$rk->save();

	      	$rkl = new RencanaKerjaLog;
	      	$rkl->rk_id 			= $rk->id;
	      	$rkl->jam 				= date('Y-m-d H:i:s');
	      	$rkl->user_id 			= $user->id;
	      	$rkl->user_nama 	 	= $user->name;
	      	$rkl->status_id 		= $rk->status_id;
	      	$rkl->status_nama 		= $rk->status_nama;
	      	$rkl->event 			= 'Report Spraying';
	      	$rkl->catatan 			= '';
	      	$rkl->status_id_lama 	= $status_id_lama;
	      	$rkl->status_nama_lama 	= $status_nama_lama;
	      	$rkl->save();

	      	DB::commit();
	      	return response()->json([
	        	'status' 	=> true, 
	        	'message' 	=> 'Submitted successfully', 
	        	'data' 		=> null
	      	]);
	    } catch(Exception $e){
	      	DB::rollback(); 
	      	return response()->json([
	        	'status' 	=> false, 
	        	'message' 	=> $e->getMessage(), 
	        	'data' 		=> null
	      	]);
	    }
	}

    public function guard(){
        return Auth::guard('api');
    }
}
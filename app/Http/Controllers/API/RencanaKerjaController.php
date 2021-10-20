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
use App\Model\Nozzle;
use App\Model\VolumeAir;
use App\Model\ReportParameter;
use App\Model\ReportParameterStandard;
use App\Model\ReportParameterBobot;
use App\Model\ReportStatus;

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
    	$list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->orderBy('ritase', 'ASC')->orderBy('id', 'ASC')->get();
		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> $rk
    	]);
	} 

	public function hasil(Request $request){
    	$rk = RencanaKerja::find($request->id);
    	$rks = RencanaKerjaSummary::where('rk_id', $rk->id)->orderBy('ritase', 'ASC')->orderBy('id', 'ASC')->get();
    	$list_rks = [];
        foreach($rks as $v){
            $list_rks[$v->ritase][] = $v;
        }
        $list_rks2 = [];
    	foreach($list_rks AS $k=>$v){
    		$o = [];
    		$o['ritase'] = $k;
    		foreach($v AS $k2=>$v2) {
    			$o['nilai_'.$v2->parameter_id] = $v2->nilai;
    			$o['kualitas_'.$v2->parameter_id] = $v2->kualitas;
    		}
    		$list_rks2[] = (object) $o;
    	}
		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> [
      			'rk' 	=> $rk,
      			'rks'	=> $list_rks2
      		]
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
		$list_volume_air = VolumeAir::orderBy('volume', 'ASC')->get(['id', 'volume']);
		$list_nozzle 	 = Nozzle::orderBy('nama', 'ASC')->get(['id', 'nama']);
		$data = [
			'list_shift'		=> $list_shift,
			'list_lokasi'		=> $list_lokasi,
			'list_aktivitas'	=> $list_aktivitas,
			'list_unit'			=> $list_unit,
			'list_operator' 	=> $list_operator,
			'list_driver' 		=> $list_driver,
			'list_volume_air' 	=> $list_volume_air,
			'list_nozzle'		=> $list_nozzle
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
	      	$rk->nozzle_id  				= $request->unit_id;
	      	$nozzle 		 		 	= Nozzle::find($request->nozzle_id);
	      	$rk->nozzle_nama		 	= $nozzle->nama;
	      	$rk->volume_id				= $request->volume_id;
	      	$volume_air 		 		= VolumeAir::find($request->volume_id);
	      	$rk->volume		 			= $volume_air->volume;
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
    	set_time_limit(0);
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
            $aktivitas = Aktivitas::find($rk->aktivitas_id);
            $list_rs = ReportStatus::get();
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
        $kecepatan_total    = round($jarak_tempuh_total / ($waktu_tempuh_total/3600),2); 
        $luas_spray_total = ($jarak_spray_kanan_total * 1000 * 18 + $jarak_spray_kiri_total * 1000 * 18)/10000;

        $area_not_spray = 0;
        $this->saveRKS($rk->id, 999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 7, $area_not_spray);
        $this->saveRKS($rk->id, 999999, $aktivitas->grup_id, $rk->aktivitas_id, $rk->nozzle_id, $rk->volume_id, 999999, 0);

        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->orderBy('ritase', 'ASC')->orderBy('id', 'ASC')->get();

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
				'summary'	=> $list_rks
			]
    	]);
	} 

	function saveRKS($rencana_kerja_id, $ritase, $grup_aktivitas_id, $aktivitas_id, $nozzle_id, $volume_id, $parameter_id, $realisasi) {
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
        $rks->realisasi     = $realisasi;
        $rks->nilai         = $nilai;
        $rks->bobot         = $bobot;
        $rks->nilai_bobot   = $nilai_bobot;
        $rks->kualitas      = $kualitas;
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
<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaBahan;
use App\Model\RencanaKerjaLog;
use App\Model\RencanaKerjaSummary;
use App\Model\Shift;
use App\Model\Lokasi;
use App\Model\Aktivitas;
use App\Model\AktivitasParameter;
use App\Model\Unit;
use App\Model\Bahan;
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
        $this->middleware('auth:api', ['except' => ['playback_view', 'map_view']]);
    }

    public function list(Request $request){
    	$user = $this->guard()->user();
        $list =RencanaKerja::where('kasie_id', $user->id)
        	->orderBy('tgl', 'DESC')
        	->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function list2(Request $request){
    	$user = $this->guard()->user();
    	$list_status = explode(',', $request->status);
    	$list_area = explode(',', $user->area);
        $list =RencanaKerja::where('tgl', $request->tgl)
        	//->where('kasie_id', $user->id)
        	->whereIn('lokasi_grup', $list_area)
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
    	$user = $this->guard()->user();
        $list =RencanaKerja::where('tgl', $request->tgl)
            ->where('operator_id', $user->id)
            ->orderBy('shift_id', 'ASC')
            ->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function sync_down(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
        $list_rk = RencanaKerja::where('updated_at', '>', $updated_at)->get();
        $list = [];
        foreach ($list_rk as $k=>$v) {
        	$v->deleted = 'N';
	      	$list[$k] = $v;
	      	$list_rkb = RencanaKerjaBahan::where('rk_id', $v->id)->get();
	      	$list[$k]->bahan = $list_rkb;
	      	$list_rks = RencanaKerjaSummary::where('rk_id', $v->id)->get();
	      	$list[$k]->summary = $list_rks;
	      	$list_rkl = RencanaKerjaLog::where('rk_id', $v->id)->get();
	      	$list[$k]->log = $list_rkl;
	    }
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
          ]);
    }

    public function sync_up(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	if($rk==null) {
	      		$rk = new RencanaKerja;
	      	}
	      	if($request->deleted=='Y') {
	      		RencanaKerjaBahan::where('rk_id', $rk->id)->delete();
	      		$rk->delete();
	      	} else {
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
			  	$rk->lokasi_grup 	= $lokasi->grup;
			  	$rk->aktivitas_id  	= $request->aktivitas_id;
			  	$aktivitas 		 	= Aktivitas::find($request->aktivitas_id);
			  	$rk->aktivitas_kode = $aktivitas->kode;
			  	$rk->aktivitas_nama = $aktivitas->nama;
			  	$rk->unit_id  				= $request->unit_id;
			  	$unit 		 				= Unit::find($request->unit_id);
			  	$rk->unit_label				= $unit->label;
			  	$rk->unit_source_device_id 	= $unit->source_device_id;
			  	$rk->nozzle_id  			= $request->nozzle_id;
			  	$nozzle 		 		 	= Nozzle::find($request->nozzle_id);
			  	$rk->nozzle_nama		 	= $nozzle->nama;
			  	$rk->volume_id				= $request->volume_id;
			  	$volume_air 		 		= VolumeAir::find($request->volume_id);
			  	$rk->volume		 			= $volume_air->volume;
			  	$rk->operator_id 			= $request->operator_id;
			  	$operator 		 			= User::find($request->operator_id);
			  	$rk->operator_empid 		= $operator->employee_id;
			  	$rk->operator_nama 			= $operator->name;
			  	$rk->mixing_operator_id 	= $request->mixing_operator_id;
			  	$mixing_operator 			= User::find($request->mixing_operator_id);
			  	$rk->mixing_operator_empid 	= $mixing_operator->employee_id;
			  	$rk->mixing_operator_nama 	= $mixing_operator->name;
			  	$rk->driver_id 				= $request->driver_id;
			  	$driver 		 			= User::find($request->driver_id);
			  	$rk->driver_empid 			= $driver->employee_id;
			  	$rk->driver_nama 			= $driver->name;
			  	$rk->kasie_id  				= $user->id;
			  	$rk->kasie_empid 			= $user->employee_id;
			  	$rk->kasie_nama 			= $user->name;
			  	$rk->status_id 				= $request->status_id;
			  	$status 		 			= Status::find($rk->status_id);
			  	$rk->status_nama 			= $status->nama;
			  	$rk->status_urutan 			= $status->urutan;
			  	$rk->status_color 			= $status->color;
			    $rk->om_status_id           = $request->om_status_id;
			    $status                     = Status::find($rk->om_status_id);
			    $rk->om_status_nama         = $status->nama;
			    $rk->om_status_urutan       = $status->urutan;
			    $rk->om_status_color        = $status->color;
			  	$rk->save();
			  	RencanaKerjaBahan::where('rk_id', $request->id)->delete();
			  	foreach($request->bahan as $v){
	                $v = (object) $v;
	                if(!empty($v->qty)){
	                    $rkb                        = new RencanaKerjaBahan;
	                    $rkb->rk_id     = $rk->id;
	                    $rkb->bahan_id              = $v->bahan_id;
	                    $rkb->bahan_kode            = $v->bahan_kode;
	                    $rkb->bahan_nama            = $v->bahan_nama;
	                    $rkb->qty                   = $v->qty;
	                    $rkb->uom                   = $v->uom;
	                    $rkb->save();
	                }
	            }
	      	}
	      	DB::commit();
	      	return response()->json([
	        	'status' => true, 
	        	'message' => 'Submitted successfully', 
	        	'data' => $rk
	      	]);
	    } catch(Exception $e){
	      DB::rollback(); 
	      return response()->json([
	        'status' => false, 
	        'message' => $e->getMessage(), 
	        'data' => null
	      ]);
	    }
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
    			$o['nilai_'.$v2->parameter_id] = number_format($v2->nilai,0);
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
    	$std =  ReportParameterStandard::join('report_parameter_standard_detail AS d', 'd.report_parameter_standard_id', '=', 'report_parameter_standard.id')
                ->where('d.report_parameter_id', 1)
                ->where('report_parameter_standard.aktivitas_id', $rk->aktivitas_id)
                ->where('report_parameter_standard.nozzle_id', $rk->nozzle_id)
                ->where('report_parameter_standard.volume_id', $rk->volume_id)
                ->where('d.point', 100)
                ->first(['d.*']);
        $rk->standard_kecepatan = $std->range_1.' - '.$std->range_2;
    	$rk->standard_kecepatan_range_1 = $std->range_1;
    	$rk->standard_kecepatan_range_2 = $std->range_2;
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
        $unit->nozzle_kanan             = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_1) ? 'On' : 'Off';
        $unit->nozzle_kiri              = $lacak != null && !empty($lacak->din_3) && !empty($lacak->din_2) ? 'On' : 'Off';

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
		$user = $this->guard()->user();
		$list_shift 	= Shift::orderBy('id', 'ASC')->get(['id', 'nama']);
		$list_lokasi 	= Lokasi::where('grup', $user->area)
			->orderBy('kode', 'ASC')
			->get(['id', 'kode', 'nama', 'lsbruto', 'lsnetto']);
		$list_aktivitas = Aktivitas::orderBy('kode', 'ASC')->get(['id', 'kode', 'nama']);
		$list_unit 		= Unit::where('pg', $user->area)->orderBy('label', 'ASC')->get(['id', 'label']);
		$list_operator 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_SPRAY_OPERATOR')
			->whereIn('users.area', explode(',', $user->area))
			->where('users.status','active')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
		$list_driver 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_SPRAY_DRIVER')
			->whereIn('users.area', explode(',', $user->area))
			->where('users.status','active')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
		$list_mixing_operator 	= User::join('roles AS r', 'r.id', '=', 'users.role_id')
			->where('r.code', 'MBL_MIXING_OPERATOR')
			->whereIn('users.area', explode(',', $user->area))
			->where('users.status','active')
			->orderBy('users.name', 'ASC')
			->get(['users.id', 'users.name AS nama']);
		$list_volume_air= VolumeAir::orderBy('volume', 'ASC')->get(['id', 'volume']);
		$list_nozzle 	= Nozzle::orderBy('nama', 'ASC')->get(['id', 'nama']);
		$list_bahan 	= Bahan::orderBy('nama', 'ASC')->get(['id', 'kode', 'nama', 'uom']);
		$list_standard 	= ReportParameterStandard::leftJoin('aktivitas AS a', 'a.id', '=', 'report_parameter_standard.aktivitas_id')
            ->leftJoin('nozzle AS n', 'n.id', '=', 'report_parameter_standard.nozzle_id')
            ->leftJoin('volume_air AS v', 'v.id', '=', 'report_parameter_standard.volume_id')
            ->get(['report_parameter_standard.*', 'a.nama AS aktivitas_nama', 'n.nama AS nozzle_nama', 'v.volume']);
		$data = [
			'list_shift'		=> $list_shift,
			'list_lokasi'		=> $list_lokasi,
			'list_aktivitas'	=> $list_aktivitas,
			'list_unit'			=> $list_unit,
			'list_operator' 	=> $list_operator,
			'list_mixing_operator' 	=> $list_mixing_operator,
			'list_driver' 		=> $list_driver,
			'list_volume_air' 	=> $list_volume_air,
			'list_nozzle'		=> $list_nozzle,
			'list_bahan'		=> $list_bahan,
			'list_standard'		=> $list_standard
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
		  	$rk->lokasi_grup 	= $lokasi->grup;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$aktivitas 		 	= Aktivitas::find($request->aktivitas_id);
	      	$rk->aktivitas_kode = $aktivitas->kode;
	      	$rk->aktivitas_nama = $aktivitas->nama;
	      	$rk->unit_id  				= $request->unit_id;
	      	$unit 		 				= Unit::find($request->unit_id);
	      	$rk->unit_label				= $unit->label;
	      	$rk->unit_source_device_id 	= $unit->source_device_id;
	      	$rk->nozzle_id  			= $request->nozzle_id;
	      	$nozzle 		 		 	= Nozzle::find($request->nozzle_id);
	      	$rk->nozzle_nama		 	= $nozzle->nama;
	      	$rk->volume_id				= $request->volume_id;
	      	$volume_air 		 		= VolumeAir::find($request->volume_id);
	      	$rk->volume		 			= $volume_air->volume;
	      	$rk->operator_id 			= $request->operator_id;
	      	$operator 		 			= User::find($request->operator_id);
	      	$rk->operator_empid 		= $operator->employee_id;
	      	$rk->operator_nama 			= $operator->name;
	      	$rk->mixing_operator_id 	= $request->mixing_operator_id;
	      	$mixing_operator 			= User::find($request->mixing_operator_id);
	      	$rk->mixing_operator_empid 	= $mixing_operator->employee_id;
	      	$rk->mixing_operator_nama 	= $mixing_operator->name;
	      	$rk->driver_id 				= $request->driver_id;
	      	$driver 		 			= User::find($request->driver_id);
	      	$rk->driver_empid 			= $driver->employee_id;
	      	$rk->driver_nama 			= $driver->name;
	      	$rk->kasie_id  				= $user->id;
	      	$rk->kasie_empid 			= $user->employee_id;
	      	$rk->kasie_nama 			= $user->name;
	      	$rk->status_id 				= 1;
	      	$status 		 			= Status::find(1);
	      	$rk->status_nama 			= $status->nama;
	      	$rk->status_urutan 			= $status->urutan;
	      	$rk->status_color 			= $status->color;
            $status                     = Status::find(5);
            $rk->om_status_id           = $status->id;
            $rk->om_status_nama         = $status->nama;
            $rk->om_status_urutan       = $status->urutan;
            $rk->om_status_color        = $status->color;
	      	$rk->save();

            foreach($request->bahan as $v){
                $v = (object) $v;
                if(!empty($v->qty)){
                    $rkb                        = new RencanaKerjaBahan;
                    $rkb->rk_id     = $rk->id;
                    $rkb->bahan_id              = $v->bahan_id;
                    $rkb->bahan_kode            = $v->bahan_kode;
                    $rkb->bahan_nama            = $v->bahan_nama;
                    $rkb->qty                   = $v->qty;
                    $rkb->uom                   = $v->uom;
                    $rkb->save();
                }
            }

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
		  	$rk->lokasi_grup 	= $lokasi->grup;
	      	$rk->aktivitas_id  	= $request->aktivitas_id;
	      	$aktivitas 		 	= Aktivitas::find($request->aktivitas_id);
	      	$rk->aktivitas_kode = $aktivitas->kode;
	      	$rk->aktivitas_nama = $aktivitas->nama;
	      	$rk->unit_id  				= $request->unit_id;
	      	$unit 		 				= Unit::find($request->unit_id);
	      	$rk->unit_label				= $unit->label;
	      	$rk->unit_source_device_id 	= $unit->source_device_id;
	      	$rk->nozzle_id  			= $request->nozzle_id;
	      	$nozzle 		 		 	= Nozzle::find($request->nozzle_id);
	      	$rk->nozzle_nama		 	= $nozzle->nama;
	      	$rk->volume_id				= $request->volume_id;
	      	$volume_air 		 		= VolumeAir::find($request->volume_id);
	      	$rk->volume		 			= $volume_air->volume;
	      	$rk->operator_id 			= $request->operator_id;
	      	$operator 		 			= User::find($request->operator_id);
	      	$rk->operator_empid 		= $operator->employee_id;
	      	$rk->operator_nama 			= $operator->name;
	      	$rk->driver_id 				= $request->driver_id;
	      	$driver 		 			= User::find($request->driver_id);
	      	$rk->driver_empid 			= $driver->employee_id;
	      	$rk->driver_nama 			= $driver->name;
	      	$rk->kasie_id  				= $user->id;
	      	$rk->kasie_empid 			= $user->employee_id;
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
	      	$geofenceHelper = new GeofenceHelper;
	      	$list_polygon = $geofenceHelper->createListPolygon();
	      	$lacak = Lacak::where('ident', $rk->unit_source_device_id)
	      		->orderBy('timestamp', 'DESC')
	      		->limit(1)
	      		->first();
            $position_latitude        = $lacak != null ? $lacak->position_latitude : 0;
            $position_longitude        = $lacak != null ? $lacak->position_longitude : 0;
        	$lokasi = $geofenceHelper->checkLocation($list_polygon, $position_latitude, $position_longitude);
        	$lokasi = !empty($lokasi) ? substr($lokasi, 0, strlen($lokasi)-2) : '';
	      	if($lokasi!=$rk->lokasi_kode){
	      		return response()->json([
		        	'status' 	=> false, 
		        	'message' 	=> 'Lokasi Anda ['.$lokasi.'] tidak sesuai dengan Lokasi di Rencana Kerja ['.$rk->lokasi_kode.']', 
		        	'data' 		=> null
		      	]);
	      	}
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

	public function summary(Request $request) {
        $rk = RencanaKerja::find($request->id);
        $rks = RencanaKerjaSummary::where('rk_id', $rk->id)
            ->orderBy('ritase', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
        $lacak = Lacak::where('ident', $rk->unit_source_device_id)
            ->where('timestamp', '>=', strtotime($rk->jam_mulai))
            ->where('timestamp', '<=', strtotime($rk->jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude AS latitude', 'position_longitude AS longitude', 'position_direction AS direction', 'din_1', 'din_2', 'din_3']);

		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> [
				'rk' 		=> $rk,
				'rks' 		=> $rks,
				'lacak'		=> $lacak
			]
    	]);
    }

	public function summary_old(Request $request){
    	set_time_limit(0);
        $rk = RencanaKerja::find($request->id);
        if(empty($rk->jam_laporan)) {
            $aktivitas = Aktivitas::find($rk->aktivitas_id);
            $list_rs = ReportStatus::get();
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

	        $list_rks = RencanaKerjaSummary::where('rk_id', $rk->id)->orderBy('ritase', 'ASC')->orderBy('id', 'ASC')->get();

	        // // Jarak tempuh: Dihitung mulai spray sd stop spray ( m)
	        // //Luas aplikasi spray total: (Jarak tempuh x 1000) x (36/10.000)
	        // // Area overlapping: 1 - ( luas peta lok/ luas aplikasi spray total)
	        // // Ketepatan dosis spray(%) 100%  - prosen overlapping
	        // // Satu ritase: Waktu, jarak dan lebar semprot per satu tangki boom sprayer ( 8000 liter)
	        // // Waktu tunggu antar rit: Waktu yg dihasilkan saat tidak ada aktivitas spray dr rit sblmnya ke start spray rit berikutnya
        }

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
    			$o['nilai_'.$v2->parameter_id] = number_format($v2->nilai,0);
    			$o['kualitas_'.$v2->parameter_id] = $v2->kualitas;
    		}
    		$list_rks2[] = (object) $o;
    	}


        $lacak = Lacak::where('ident', $rk->unit_source_device_id)
            ->where('timestamp', '>=', strtotime($rk->jam_mulai))
            ->where('timestamp', '<=', strtotime($rk->jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude AS latitude', 'position_longitude AS longitude', 'position_direction AS direction']);

		return response()->json([
      		'status' 	=> true, 
      		'message' 	=> '', 
      		'data' 		=> [
				'rk' 		=> $rk,
				'rks' 		=> $list_rks2,
				'lacak'		=> $lacak
			]
    	]);
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

	// Finish Spraying
  	public function finish_spraying(Request $request){
	    $user = $this->guard()->user();
	    DB::beginTransaction();
	    try {
	      	$rk = RencanaKerja::find($request->id);
	      	$status_id_lama 	= $rk->status_id;
	      	$status_nama_lama 	= $rk->status_nama;
	      	$rk->status_id 		= 4;
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
	      	$status_nama_lama 	= $rk->status_nama;
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

    public function playback_view(Request $request) {
        $id = !empty($request->id) ? $request->id :0;
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $interval = !empty($request->interval) ? $request->interval : 1000;
        $unit = Unit::find($rk->unit_id);
        $list_interval = [];
        for($i=1; $i<=10; $i++){
            $list_interval[$i*100] = ($i/10).' Detik';
        }
        $list = KoordinatLokasi::orderBy('lokasi', 'ASC')
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        $list_polygon = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
            if(array_key_exists($idx, $list_polygon)){
                $list_polygon[$idx][] = $v->latd." ".$v->long;
            } else {
                $list_polygon[$idx] = [$v->latd." ".$v->long];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $geofenceHelper = new GeofenceHelper;
        $durasi = strtotime($jam_selesai) - strtotime($jam_mulai) + 1;
        $lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp', 'din_1', 'din_2', 'din_3']);
        $list_lacak = [];
        foreach($lacak as $v){
            $v->lokasi = '';//$geofenceHelper->checkLocation($list_polygon, $v->position_latitude, $v->position_longitude);
            $v->lokasi = !empty($v->lokasi) ? substr($v->lokasi,0,strlen($v->lokasi)-2) : '';
            $v->progress_time = doubleval($v->timestamp) - strtotime($jam_mulai);
            $v->progress_time_pers = ($v->progress_time / $durasi) * 100 ;
            $v->timestamp_2 = date('H:i:s', $v->timestamp);
            $list_lacak[] = $v;
        }
        return view('api.rencana_kerja.playback', [
            'unit'          => $unit,
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi),
            'list_interval' => $list_interval,
            'interval'      => $interval,
            'durasi'        => $durasi
        ]);
    }

    public function map_view(Request $request) {
        $id = !empty($request->id) ? $request->id :0;
        $rk = RencanaKerja::find($id);
        $jam_mulai = $rk->jam_mulai;
        $jam_selesai = $rk->jam_selesai;
        $unit = Unit::find($rk->unit_id);
        $list = KoordinatLokasi::where('lokasi', $rk->lokasi_kode)
            ->orderBy('bagian', 'ASC')
            ->orderBy('posnr', 'ASC')
            ->get();
        $list_lokasi = [];
        foreach($list as $v){
            $idx = $v->lokasi.'_'.$v->bagian;
            if(array_key_exists($idx, $list_lokasi)){
                $list_lokasi[$idx]['koordinat'][] = ['lat' => $v->latd, 'lng' => $v->long];
            } else {
                $list_lokasi[$idx] = ['nama' => $v->lokasi, 'koordinat' => [['lat' => $v->latd, 'lng' => $v->long]]];
            }
        }
        $list_lokasi = array_values($list_lokasi);
        $list_lacak = Lacak::where('ident', $unit->source_device_id)
            ->where('timestamp', '>=', strtotime($jam_mulai))
            ->where('timestamp', '<=', strtotime($jam_selesai))
            ->orderBy('timestamp', 'ASC')
            ->get(['position_latitude', 'position_longitude', 'position_altitude', 'position_direction', 'position_speed', 'ain_1', 'ain_2', 'timestamp', 'din_1', 'din_2', 'din_3']);
        return view('api.rencana_kerja.map', [
            'rk'            => $rk, 
            'list_lacak'    => json_encode($list_lacak),
            'list_lokasi'   => json_encode($list_lokasi)
        ]);
    }

    // Delete
    public function delete(Request $request){
        $user = $this->guard()->user();
        DB::beginTransaction();
        try {
            $rk = RencanaKerja::find($request->id);
            $rk->delete();
            RencanaKerjaLog::where('rk_id', $rk->id)->delete();
            RencanaKerjaBahan::where('rk_id', $rk->id)->delete();
            DB::commit();
            return response()->json([
                'status'    => true, 
                'message'   => 'Deleted successfully', 
                'data'      => null
            ]);
        } catch(Exception $e){
            DB::rollback(); 
            return response()->json([
                'status'    => false, 
                'message'   => $e->getMessage(), 
                'data'      => null
            ]);
        }
    }

    public function guard(){
        return Auth::guard('api');
    }
}
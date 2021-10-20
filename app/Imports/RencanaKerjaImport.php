<?php

namespace App\Imports;

use App\Model\RencanaKerja;
use App\Model\RencanaKerjaLog;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\Shift;
use App\Model\Unit;
use App\Model\Aktivitas;
use App\Model\Lokasi;
use App\Model\User;
use App\Model\Status;
use App\Model\Nozzle;
use App\Model\VolumeAir;


class RencanaKerjaImport implements ToModel, WithHeadingRow
{
    protected $kasie_id;
    function __construct($kasie_id) {
        $this->kasie_id = $kasie_id;
    }

    public function model(array $row)
    { 
        // dd($row);    
        $tgl = ($row["tanggal"] - 25569) * 86400;
        $shift = Shift::where('nama', $row["shift"])->first();
        $lokasi = Lokasi::where('nama', $row["lokasi"])->first();
        $aktivitas = Aktivitas::where('nama', $row["aktivitas"])->first();
        $nozzle = Nozzle::where('nama', $row["nozzle"])->first();
        $unit = Unit::where('label', $row["unit"])->first();
        $operator = User::where('employee_id', $row["operator"])->first();
        $driver = User::where('employee_id', $row["driver"])->first();
        $kasie = User::find($this->kasie_id);
        $lokasi_lsbruto = !empty($row["luas_bruto"]) ? $row["luas_bruto"] : $lokasi->lsbruto;
        $lokasi_lsnetto = !empty($row["luas_netto"]) ? $row["luas_netto"] : $lokasi->lsnetto;
        $status = Status::find(1);
        return new RencanaKerja([
            'tgl'             => gmdate('Y-m-d',$tgl),
            'shift_id'          => $shift->id,
            'shift_nama'        => $shift->nama,
            'lokasi_id'         => $lokasi->id,
            'lokasi_kode'       => $lokasi->kode,
            'lokasi_nama'       => $lokasi->nama,
            'lokasi_lsbruto'    => $lokasi_lsbruto,
            'lokasi_lsnetto'    => $lokasi_lsnetto,
            'aktivitas_id'      => $aktivitas->id,
            'aktivitas_kode'    => $aktivitas->kode,
            'aktivitas_nama'    => $aktivitas->nama,
            'nozzle_id'             => 1,
            'nozzle_nama'           => $row["nozzle"],
            'unit_id'               => $unit->id,
            'unit_label'            => $unit->label,
            'unit_source_device_id' => $unit->source_device_id,
            'volume'                => $row["volume_air"],
            'operator_id'           => $operator->id,
            'operator_empid'        => $operator->employee_id,
            'operator_nama'         => $operator->name,
            'driver_id'             => $driver->id,
            'driver_empid'          => $driver->employee_id,
            'driver_nama'           => $driver->name,
            'kasie_id'              => $kasie->id,
            'kasie_empid'           => $kasie->employee_id,
            'kasie_nama'            => $kasie->name,
            'status_id'             => $aktivitas->id,
            'status_urutan'         => $status->urutan,
            'status_nama'           => $status->nama,
            'status_color'          => $status->color,  
        ],
        [
            $rk = DB::table('rencana_kerja')->latest('id')->first(),
            $id_rk = ($rk->id + 1),
            $rkl = new RencanaKerjaLog,
            $rkl->rk_id 			= $id_rk,
            $rkl->jam 				= date('Y-m-d H:i:s'),
            $rkl->user_id 			= $kasie->id,
            $rkl->user_nama 	 	= $kasie->name,
            $rkl->status_id 		= $status->id,
            $rkl->status_nama 		= $status->nama,
            $rkl->event 			= 'Create',
            $rkl->catatan 			= '',
            $rkl->status_id_lama 	= 0,
            $rkl->status_nama_lama 	= '',
            $rkl->save(),
        ]);

    }
}

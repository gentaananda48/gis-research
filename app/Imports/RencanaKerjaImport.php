<?php

namespace App\Imports;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
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
use App\Model\Bahan;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaLog;
use App\Model\OrderMaterial;
use App\Model\OrderMaterialBahan;
use App\Model\OrderMaterialLog;

class RencanaKerjaImport implements ToCollection, WithHeadingRow
{
    protected $kasie_id;
    function __construct($kasie_id) {
        $this->kasie_id = $kasie_id;
    }

    public function collection(Collection $rows) { 
        DB::beginTransaction();
        try {
            foreach ($rows as $row){
                $tgl = ($row["tanggal"] - 25569) * 86400;
                $shift = Shift::where('nama', $row["shift"])->first();
                $lokasi = Lokasi::where('nama', $row["lokasi"])->first();
                $aktivitas = Aktivitas::where('nama', $row["aktivitas"])->first();
                $nozzle = Nozzle::where('nama', $row["nozzle"])->first();
                $unit = Unit::where('label', $row["unit"])->first();
                $operator = User::where('employee_id', $row["operator"])->first();
                $mixing_operator = User::where('employee_id', $row["mixing_operator"])->first();
                $driver = User::where('employee_id', $row["driver"])->first();
                $volumeAir = VolumeAir::where('volume', $row["volume_air"])->first();
                $kasie = User::find($this->kasie_id);
                $lokasi_lsbruto = !empty($row["luas_bruto"]) ? $row["luas_bruto"] : $lokasi->lsbruto;
                $lokasi_lsnetto = !empty($row["luas_netto"]) ? $row["luas_netto"] : $lokasi->lsnetto;
                $status = Status::find(1);

                $rk = new RencanaKerja;
                $rk->tgl                    = gmdate('Y-m-d',$tgl);
                $rk->shift_id               = $shift->id;
                $rk->shift_nama             = $shift->nama;
                $rk->lokasi_id              = $lokasi->id;
                $rk->lokasi_kode            = $lokasi->kode;
                $rk->lokasi_nama            = $lokasi->nama;
                $rk->lokasi_lsbruto         = $lokasi_lsbruto;
                $rk->lokasi_lsnetto         = $lokasi_lsnetto;
                $rk->aktivitas_id           = $aktivitas->id;
                $rk->aktivitas_kode         = $aktivitas->kode;
                $rk->aktivitas_nama         = $aktivitas->nama;
                $rk->nozzle_id              = $nozzle->id;
                $rk->nozzle_nama            = $nozzle->nama;
                $rk->unit_id                = $unit->id;
                $rk->unit_label             = $unit->label;
                $rk->unit_source_device_id  = $unit->source_device_id;
                $rk->volume_id              = $volumeAir->id;
                $rk->volume                 = $volumeAir->volume;
                $rk->operator_id            = $operator->id;
                $rk->operator_empid         = $operator->employee_id;
                $rk->operator_nama          = $operator->name;
                $rk->mixing_operator_id     = $mixing_operator->id;
                $rk->mixing_operator_empid  = $mixing_operator->employee_id;
                $rk->mixing_operator_nama   = $mixing_operator->name;
                $rk->driver_id              = $driver->id;
                $rk->driver_empid           = $driver->employee_id;
                $rk->driver_nama            = $driver->name;
                $rk->kasie_id               = $kasie->id;
                $rk->kasie_empid            = $kasie->employee_id;
                $rk->kasie_nama             = $kasie->name;
                $rk->status_id              = $status->id;
                $rk->status_urutan          = $status->urutan;
                $rk->status_nama            = $status->nama;
                $rk->status_color           = $status->color;
                $rk->save();

                $rkl = new RencanaKerjaLog;
                $rkl->rk_id             = $rk->id;
                $rkl->jam               = date('Y-m-d H:i:s');
                $rkl->user_id           = $kasie->id;
                $rkl->user_nama         = $kasie->name;
                $rkl->status_id         = $status->id;
                $rkl->status_nama       = $status->nama;
                $rkl->event             = 'Create';
                $rkl->catatan           = '';
                $rkl->status_id_lama    = 0;
                $rkl->status_nama_lama  = '';
                $rkl->save();

                $om                         = new OrderMaterial;
                $om->rk_id                  = $rk->id;
                $om->tanggal                = $rk->tgl;
                $om->unit_id                = $rk->unit_id;
                $om->unit_label             = $rk->unit_label;
                $om->aktivitas_id           = $rk->aktivitas_id;
                $om->aktivitas_kode         = $rk->aktivitas_kode;
                $om->aktivitas_nama         = $rk->aktivitas_nama;
                $om->lokasi_id              = $rk->lokasi_id;
                $om->lokasi_kode            = $rk->lokasi_kode;
                $om->lokasi_nama            = $rk->lokasi_nama;
                $om->kasie_id               = $rk->kasie_id;
                $om->kasie_empid            = $rk->kasie_empid;
                $om->kasie_nama             = $rk->kasie_nama;
                $om->operator_id            = $rk->operator_id;
                $om->operator_empid         = $rk->operator_empid;
                $om->operator_nama          = $rk->operator_nama;
                $om->mixing_operator_id     = $rk->mixing_operator_id;
                $om->mixing_operator_empid  = $rk->mixing_operator_empid;
                $om->mixing_operator_nama   = $rk->mixing_operator_nama;
                $status                     = Status::find(5);
                $om->status_id              = $status->id;
                $om->status_nama            = $status->nama;
                $om->status_urutan          = $status->urutan;
                $om->status_color           = $status->color;
                $om->save();

                $list_bahan = Bahan::get();
                foreach($list_bahan as $bahan){
                    Log::info($bahan->kode);
                    Log::info($row[strtolower($bahan->kode)]);
                    if(!empty($row[strtolower($bahan->kode)])){
                        $omb                        = new OrderMaterialBahan;
                        $omb->order_material_id     = $om->id;
                        $omb->bahan_id              = $bahan->id;
                        $omb->bahan_kode            = $bahan->kode;
                        $omb->bahan_nama            = $bahan->nama;
                        $omb->qty                   = $row[strtolower($bahan->kode)];
                        $omb->uom                   = $bahan->uom;
                        $omb->save();
                    }
                }

                $oml                     = new OrderMaterialLog;
                $oml->order_material_id  = $om->id;
                $oml->jam                = date('Y-m-d H:i:s');
                $oml->user_id            = $rk->kasie_id;
                $oml->user_nama          = $rk->kasie_nama;
                $oml->status_id          = $status->id;
                $oml->status_nama        = $status->nama;
                $oml->status_id_lama     = 0;
                $oml->status_nama_lama   = '';
                $oml->event              = 'Create';
                $oml->catatan            = '';           
                $oml->save();
            }
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
    }
}

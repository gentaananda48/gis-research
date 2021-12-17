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
use App\Model\RencanaKerjaBahan;

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
                $rk->lokasi_grup            = $lokasi->grup;
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
                
                $list_bahan = Bahan::get();
                foreach($list_bahan as $bahan){
                    if(!empty($row[strtolower($bahan->kode)])){
                        $rkb                        = new RencanaKerjaBahan;
                        $rkb->rk_id                 = $rk->id;
                        $rkb->bahan_id              = $bahan->id;
                        $rkb->bahan_kode            = $bahan->kode;
                        $rkb->bahan_nama            = $bahan->nama;
                        $rkb->qty                   = $row[strtolower($bahan->kode)];
                        $rkb->uom                   = $bahan->uom;
                        $rkb->save();
                    }
                }

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
            }
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
    }
}

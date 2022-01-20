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

class RencanaKerjaPPOImport implements ToCollection, WithHeadingRow
{
    protected $user_id;
    function __construct($user_id) {
        $this->user_id = $user_id;
    }

    public function collection(Collection $rows) { 
        DB::beginTransaction();
        try {
            $user = User::find($this->user_id);
            foreach ($rows as $row){
                $tgl = ($row["tanggal"] - 25569) * 86400;
                $waktu = !empty($row["waktu"]) ? $row["waktu"] : '';
                $lokasi = Lokasi::where('nama', $row["lokasi"])->first();
                $aktivitas = Aktivitas::where('kode', $row["kode_aktivitas"])->first();
                $unit = Unit::where('label', $row["unit"])->first();
                $volumeAir = VolumeAir::where('volume', $row["volume_air"])->first();
                $kasie = User::where('employee_id', $row["indeks_kasie"])->first();
                $lokasi_lsbruto = !empty($row["luas_bruto"]) ? $row["luas_bruto"] : $lokasi->lsbruto;
                $lokasi_lsnetto = !empty($row["luas_netto"]) ? $row["luas_netto"] : $lokasi->lsnetto;
                $status = Status::find(1);

                $rk = new RencanaKerja;
                $rk->tgl                    = gmdate('Y-m-d',$tgl);
                $rk->waktu              	= $waktu;
                $rk->shift_id               = null;
                $rk->shift_nama             = null;
                $rk->lokasi_id              = $lokasi->id;
                $rk->lokasi_kode            = $lokasi->kode;
                $rk->lokasi_nama            = $lokasi->nama;
                $rk->lokasi_grup            = $lokasi->grup;
                $rk->lokasi_lsbruto         = $lokasi_lsbruto;
                $rk->lokasi_lsnetto         = $lokasi_lsnetto;
                $rk->aktivitas_id           = $aktivitas->id;
                $rk->aktivitas_kode         = $aktivitas->kode;
                $rk->aktivitas_nama         = $aktivitas->nama;
                $rk->nozzle_id              = null;
                $rk->nozzle_nama            = null;
                $rk->unit_id                = $unit->id;
                $rk->unit_label             = $unit->label;
                $rk->unit_source_device_id  = $unit->source_device_id;
                $rk->volume_id              = $volumeAir->id;
                $rk->volume                 = $volumeAir->volume;
                $rk->operator_id            = null;
                $rk->operator_empid         = null;
                $rk->operator_nama          = null;
                $rk->mixing_operator_id     = null;
                $rk->mixing_operator_empid  = null;
                $rk->mixing_operator_nama   = null;
                $rk->driver_id              = null;
                $rk->driver_empid           = null;
                $rk->driver_nama            = null;
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
                $rkl->user_id           = $user->id;
                $rkl->user_nama         = $user->name;
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

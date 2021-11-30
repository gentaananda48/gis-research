<?php

namespace App\Exports;

use App\Model\RencanaKerja;
use App\Model\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RencanaKerjaExport implements FromCollection, WithHeadings {

	protected $request;
    protected $kasie_id;
 	function __construct($request, $kasie_id) {
		$this->request = $request;
        $this->kasie_id = $kasie_id;
	}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() {
    	$request = $this->request;
        $kasie_id = $this->kasie_id;
        $user = User::find($kasie_id);
    	$query = RencanaKerja::selectRaw("id, tgl, shift_nama AS shift, lokasi_kode, lokasi_nama, lokasi_lsbruto, lokasi_lsnetto, aktivitas_kode, aktivitas_nama, nozzle_nama AS nozzle, volume, unit_id, unit_label, unit_source_device_id, operator_nama AS operator, driver_nama AS driver, kasie_nama AS kasie, status_nama AS status, jam_mulai, jam_selesai, jam_laporan");
        if(!empty($kasie_id)){
            //$query->where('kasie_id', $kasie_id);
            $query->whereIn('lokasi_grup', explode(',', $user->area));
        }
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d', strtotime($tgl[1]));
            $query->whereBetween('tgl', [$tgl_1, $tgl_2]);
        }
        if(isset($request->shift)){
            $query->whereIn('shift_id', $request->shift);
        }
        if(isset($request->lokasi)){
            $query->whereIn('lokasi_kode', $request->lokasi);
        }
        if(isset($request->aktivitas)){
            $query->whereIn('aktivitas_kode', $request->aktivitas);
        }
        if(isset($request->unit)){
            $query->whereIn('unit_id', $request->unit);
        }
        if(isset($request->nozzle)){
            $query->whereIn('nozzle_id', $request->nozzle);
        }
        if(isset($request->volume)){
            $query->whereIn('volume', $request->volume);
        }
        if(isset($request->status)){
            $query->whereIn('status_id', $request->status);
        }
        return $query->get();
    }

    public function headings(): array {
    	return ['ID','Tanggal','Shift','Kode Lokasi','Nama Lokasi','Luas Bruto','Luas Netto','Kode Aktivitas','Nama Aktivitas','Nozzle','Volume', 'Kode Unit', 'Nama Unit', 'Device ID Unit', 'Operator', 'Driver', 'Kasie', 'Status', 'Jam Mulai', 'Jam Selesai', 'Jam Laporan'];
    }
}
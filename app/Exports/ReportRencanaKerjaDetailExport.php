<?php

namespace App\Exports;

use App\Model\VRencanaKerjaDetail;
use App\Model\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportRencanaKerjaDetailExport implements FromCollection, WithHeadings {

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
        $kasie = User::find($kasie_id);
    	$query = VRencanaKerjaDetail::selectRaw("id, tgl, shift_nama AS shift, lokasi_kode, lokasi_nama, lokasi_lsbruto, lokasi_lsnetto, aktivitas_kode, aktivitas_nama, nozzle_nama AS nozzle, volume, unit_id, unit_label, unit_source_device_id, operator_nama AS operator, driver_nama AS driver, kasie_nama AS kasie, kualitas, ritase, par_1, par_2, par_3, par_4, par_5, par_6, hasil, kualitas_detail");
        if(!empty($kasie_id)){
            //$query->where('kasie_id', $kasie_id);
    		$query->whereIn('lokasi_grup', explode(',', $kasie->area));
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
        if(isset($request->kualitas)){
            $query->whereIn('kualitas', $request->kualitas);
        }
        return $query->get();
    }

    public function headings(): array {
    	return ['ID','Tanggal','Shift','Kode Lokasi','Nama Lokasi','Luas Bruto','Luas Netto','Kode Aktivitas','Nama Aktivitas','Nozzle','Volume', 'Kode Unit', 'Nama Unit', 'Device ID Unit', 'Operator', 'Driver', 'Kasie', 'Kualitas', 'Ritase', 'Kecepatan Operasi (5.85 - 7.15)', 'Overlapping (<= 0.55)', 'Waktu Spray Per Ritase (8.865 - 10.835)', 'Ketepatan Dosis (0.8996 - 1)', 'Golden Time (16:00:00 - 11:00:00)', 'Wing Level (<= 1.43)', 'Hasil', 'Kualitas (Detail)'];
    }
}
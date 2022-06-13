<?php

namespace App\Exports;

use App\Model\RencanaKerjaSummary;
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
    	$query = RencanaKerjaSummary::selectRaw("rencana_kerja_summary.id, rencana_kerja_summary.rk_id, rencana_kerja_summary.parameter_nama, rencana_kerja_summary.standard, rencana_kerja_summary.realisasi, rencana_kerja_summary.nilai, rencana_kerja_summary.bobot, rencana_kerja_summary.nilai_bobot, rencana_kerja_summary.kualitas")
            ->join('rencana_kerja As rk', 'rk.id', '=', 'rencana_kerja_summary.rk_id')
            ->orderBy('id', 'ASC');
        if(!empty($kasie_id)){
            //$query->where('kasie_id', $kasie_id);
    		$query->whereIn('rk.lokasi_grup', explode(',', $kasie->area));
        }
        if(!empty($request->tgl)){
            $tgl = explode(' - ', $request->tgl);
            $tgl_1 = date('Y-m-d', strtotime($tgl[0]));
            $tgl_2 = date('Y-m-d', strtotime($tgl[1]));
            $query->whereBetween('rk.tgl', [$tgl_1, $tgl_2]);
        }
        if(isset($request->shift)){
            $query->whereIn('rk.shift_id', $request->shift);
        }
        if(isset($request->lokasi)){
            $query->whereIn('rk.lokasi_kode', $request->lokasi);
        }
        if(isset($request->aktivitas)){
            $query->whereIn('rk.aktivitas_kode', $request->aktivitas);
        }
        if(isset($request->unit)){
            $query->whereIn('rk.unit_id', $request->unit);
        }
        if(isset($request->nozzle)){
            $query->whereIn('rk.nozzle_id', $request->nozzle);
        }
        if(isset($request->volume)){
            $query->whereIn('rk.volume', $request->volume);
        }
        if(isset($request->kualitas)){
            $query->whereIn('rk.kualitas', $request->kualitas);
        }
        $query->orderBy('rencana_kerja_summary.id', 'ASC');
        return $query->get();
    }

    public function headings(): array {
    	return ['ID','Rencana Kerja ID','Parameter','Standard','Realisasi','Nilai','Bobot','Poin','Kualitas'];
    }
}
<?php

namespace App\Exports;

use App\Model\Bahan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TemplateRencanaKerjaPPOExport implements FromArray, WithHeadings {

	protected $request;
 	function __construct($request) {
		$this->request = $request;
	}

    public function array(): array {
        return [];
    }

    public function headings(): array {
    	$bahan = Bahan::get(['kode']);
    	$list = ['tanggal','waktu','lokasi','kode_aktivitas','unit', 'indeks_kasie', 'volume_air'];
    	foreach($bahan as $v){
    		$list[] = $v->kode;
    	}
    	return $list;
    }
}
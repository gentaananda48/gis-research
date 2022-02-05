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
    	$list = ['tanggal', 'lokasi', 'kode_aktivitas', 'volume_air'];
    	foreach($bahan as $v){
    		$list[] = $v->kode;
    	}
    	return $list;
    }
}
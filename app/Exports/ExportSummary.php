<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportSummary implements FromView {

	protected $request;
 	function __construct($request) {
		$this->request = $request;
	}

    public function array(): array {
        return [];
    }

    // public function headings(): array {
    // 	$bahan = Bahan::get(['kode']);
    // 	$list = ['tanggal', 'lokasi', 'kode_aktivitas', 'volume_air'];
    // 	foreach($bahan as $v){
    // 		$list[] = $v->kode;
    // 	}
    // 	return $list;
    // }

    public function view(): View
    {
        return view('exports.summary', [
            'summary' => $this->request['summary'],
            'date' => $this->request['date']
        ]);
    }
}
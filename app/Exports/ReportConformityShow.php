<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportConformityShow implements FromView {

	protected $request;
 	function __construct($request) {
		$this->request = $request;
	}

    public function array(): array {
        return [];
    }

    public function view(): View
    {
        return view('exports.reportConformityShow', ['report_conformities' => $this->request]);
    }
}
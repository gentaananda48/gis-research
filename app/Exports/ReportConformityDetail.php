<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportConformityDetail implements FromView {

	protected $request;
 	function __construct($request) {
		$this->request = $request;
	}

    public function array(): array {
        return [];
    }

    public function view(): View
    {
        return view('exports.reportConformityDetail', [
            'report_conformity' => $this->request['report_conformity'],
            'avgRRK' => $this->request['avgRRK'],
            'report_param_standard' => $this->request['report_param_standard'],
            'explodeRk' => $this->request['explodeRk']
        ]);
    }
}
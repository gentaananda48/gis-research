<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\BaseModel;

class ReportParameterStandardDetail extends BaseModel
{
    protected $table = 'report_parameter_standard_detail';
	
    public function reportParameter() {
        return $this->belongsTo(ReportParameter::class, 'report_parameter_id', 'id');
    }
}

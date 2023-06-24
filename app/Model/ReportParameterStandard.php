<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\BaseModel;

class ReportParameterStandard extends BaseModel
{
    protected $table = 'report_parameter_standard';

    public function reportParameterStandarDetails() {
        return $this->hasMany(ReportParameterStandardDetail::class, 'report_parameter_standard_id', 'id');
    }
	
}

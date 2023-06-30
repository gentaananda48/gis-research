<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportConformity extends Model
{
    protected $table = 'report_conformities';

    public function getStandardColor($val) {
        if($val > 90)
            return 'bg-green';
        if($val > 70 && $val < 90)
            return 'bg-lightgreen';
        if( $val > 50 && $val < 70)
            return 'bg-yellow';
        
        return 'bg-red';
    }
}

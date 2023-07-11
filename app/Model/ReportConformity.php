<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportConformity extends Model
{
    protected $table = 'report_conformities';

    public function getStandardColor($val) {
        if($val > 90)
            return 'background-color:#cbf078;';
        if($val > 70 && $val < 90)
            return 'background-color:#fcff82;';
        if( $val > 50 && $val < 70)
            return 'background-color:#f1b963;';
        
        return 'background-color:#e46161;color: white;';
    }
}

<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model {
	const UPDATED_AT = null;
	protected $guarded = []; 
    protected $table = 'cron_logs';	
}

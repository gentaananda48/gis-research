<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model {
	const UPDATED_AT = null;
	const CREATED_AT = null;
	protected $guarded = []; 
    protected $table = 'cron_logs';	
}

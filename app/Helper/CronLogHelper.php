<?php

namespace App\Helper;
use App\Model\CronLog;

class CronLogHelper {
    function create($name='', $status='', $remarks=''){
        $cron_log = new CronLog;
        $cron_log->name = $name;
        $cron_log->status = $status;
        $cron_log->remarks = $remarks;
        $cron_log->created_at = date('Y-m-d H:i:s');
        $cron_log->save();
    }
}
?>
<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\BaseModel;

class Log extends BaseModel
{
    protected $table = 'logs';
    public $timestamps = false;
}

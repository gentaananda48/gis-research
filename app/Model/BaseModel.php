<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use App\Event\GlobalEventObserver;

class BaseModel extends Model
{
    protected $guarded = [];
    protected $dates = ['created_at', 'updated_at','approved_at','start_at','end_at'];
    //protected $dateFormat = 'U';
    /*
     * this is observer global event on modele
     * it use for running function before and after CRUD
     * */

    public static function boot() {
        parent::boot();
        self::observe(new GlobalEventObserver);
    }
}

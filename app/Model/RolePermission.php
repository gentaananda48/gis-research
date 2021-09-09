<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model {
    protected $table = 'role_permission';
    public $timestamps = false;
    protected $fillable = ['role_id', 'permission_id'];
    public function roles() {
        return $this->belongsTo('App\Model\Role', 'role_id');
    }
    public function permissions() {
        return $this->belongsTo('App\Model\Permission', 'permission_id');
    }
}

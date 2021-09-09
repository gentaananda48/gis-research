<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\BaseModel;

class Permission extends BaseModel {
    protected $table = 'permissions';

    public function role_permission() {
        return $this->hasMany('App\Model\RolePermission', 'permission_id');
    }
}

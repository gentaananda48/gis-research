<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\BaseModel;

class Role extends BaseModel
{
    protected $table = 'roles';

	public function role_access() {
        return $this->hasMany('App\Model\RoleAccess', 'role_id');
    }
}

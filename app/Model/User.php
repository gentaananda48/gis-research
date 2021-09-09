<?php

namespace App\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Event\GlobalEventObserver;
use App\Model\Permission;

use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject 
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    protected $dates = ['created_at', 'updated_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 'password', 'remember_token',
        'remember_token',
    ];

    /*
     * this is observer global event on modele
     * it use for running function before and after CRUD
     *
     */

    public static function boot() {
        parent::boot();
        self::observe(new GlobalEventObserver);
    }

    public function authorizeAccess($access) {
        if($this->status=='deleted'){
            abort(401, 'This action is unauthorized. User is deleted.');
        }
        if ($this->hasAccess($access)) {
            return true;
        }
        abort(401, 'This action is unauthorized.');
    }

    public function hasAccess($access) {
        $permission = Permission::join('role_permission as rp','rp.permission_id','=','permissions.id')
            ->where('rp.role_id', $this->role_id)
            ->where('permissions.code',$access)
            ->first(['permissions.id']);
        if(!empty($permission)){
            return true;
        } else {
            return false;
        }
    }

    public function roleName(){
        return Role::find($this->role_id)->name;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    
}

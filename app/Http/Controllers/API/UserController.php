<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\User;

class UserController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        $query = User::select();
        if(!empty($request->role_code)){
        	$query->join('roles', 'roles.id', '=', 'users.role_id')
        		->where('roles.code', $request->role_code);
        }
        $list = $query->orderBy('username')->get();
        return response()->json([
            'status'    => true, 
            'message'   => 'success', 
            'data'      => $list
        ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
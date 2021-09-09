<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\Unit;

class UnitController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function list(Request $request){
        $updated_at = !empty($request->updated_at) ? $request->updated_at : '1900-01-01 00:00:00';
    	$list = Unit::where('updated_at', '>', $updated_at)->get();
        return response()->json([
            'status' => true, 
            'message' => 'success', 
            'data' => $list
          ]);
    }

    public function guard(){
        return Auth::guard('api');
    }
}
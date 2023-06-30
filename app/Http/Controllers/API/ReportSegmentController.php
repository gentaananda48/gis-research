<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\ApiDashboard;
use App\Model\ReportConformity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;

class ReportSegmentController extends Controller {
    public function __construct() {
        $this->middleware('auth:api', []);
    }

    public function all(Request $request){
        $data = array();

        try {
            // Validate the input, if necessary
            $this->validate($request, [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            // Retrieve data from the database based on the date range
            $data = ApiDashboard::when($request->has('start_date') == true && $request->has('end_date') == true, function ($query) use ($request) {
                return $query->whereBetween(\DB::raw('DATE(tanggal_aktifitas)'), [$request->start_date, $request->end_date]);
            })->when($request->has('limit'), function ($query) use ($request) {
                return $query->limit($request->limit);
            })
            ->get();

            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
                'data'      => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'    => false, 
                'line'      => $th->getLine(), 
                'message'   => $th->getMessage(), 
                'data'      => array()
            ]);
        }

    }

    public function segment(Request $request){
        $data = array();

        try {
            // Validate the input, if necessary
            // $this->validate($request, [
            //     'start_date' => 'required|date',
            //     'end_date' => 'required|date|after_or_equal:start_date',
            // ]);
            
            // Retrieve data from the database based on the date range
            $data = ReportConformity::when($request->has('start_date') && $request->has('end_date'), function ($query) use ($request) {
                return $query->whereBetween(\DB::raw('DATE(tanggal)'), [$request->start_date, $request->end_date]);
            })->when($request->has('limit'), function ($query) use ($request) {
                return $query->limit($request->limit);
            })
            ->get();

            return response()->json([
                'status'    => true, 
                'message'   => 'data', 
                'data'      => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status'    => false, 
                'line'      => $th->getLine(), 
                'message'   => $th->getMessage(), 
                'data'      => array()
            ]);
        }

    }

    public function guard(){
        return Auth::guard('api');
    }
}
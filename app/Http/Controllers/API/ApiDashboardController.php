<?php

namespace App\Http\Controllers\API;

use App\Model\ApiDashboard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Controllers\Controller;

class ApiDashboardController extends Controller
{
    public function index()
    {
        $data = ApiDashboard::all();
        return response()->json([
            'status'    => true, 
            'message' => 'success',
            'data' => $data
        ]);
    }

    public function getDataByDate(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Validate the input, if necessary
        $this->validate($request, [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Retrieve data from the database based on the date range
        $data = ApiDashboard::whereBetween('tanggal_aktifitas', [$startDate, $endDate])->get();

        // Return the data as a JSON response
        return response()->json([
            'status'    => true, 
            'message' => 'success',
            'data' => $data
        ]);
    }

}



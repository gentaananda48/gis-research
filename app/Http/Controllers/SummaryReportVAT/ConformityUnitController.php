<?php

namespace App\Http\Controllers\SummaryReportVAT;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;

class ConformityUnitController extends Controller
{
    public function index(Request $request)
    {
        // $user = $this->guard()->user();
        // $list_pg = explode(',', $user->area);
        if(!empty($request->date_range)){
            $date_range = explode(' - ', $request->date_range);
            $date1 = date('Y-m-d', strtotime($date_range[0]));
            $date2 = date('Y-m-d', strtotime($date_range[1]));
        } else {
            //$date1 = date('Y-m-d', strtotime('-6 day'));
            $date1 = date('Y-m-d');
            $date2 = date('Y-m-d');
        }

        $date_range = date('m/d/Y', strtotime($date1)).' - '.date('m/d/Y', strtotime($date2));
        $list_pg = ['PG1'=>'PG1', 'PG2'=>'PG2', 'PG3'=>'PG3'];
        $list_unit = ['All'=>'All', 'Unit 1'=>'Unit 1', 'Unit 2'=>'Unit 2'];

        return view('summary_report_vat.conformity_unit.index', [
            'date_range'    => $date_range,
            'list_pg'       => $list_pg,
            'pg'            => $request->pg,
            'list_unit'     => $list_unit,
            'unit'          => $request->unit,
        ]); 
    }

    public function show($id, Request $request)
    {
        
        $date1 = date('Y-m-01');
        $date2 = date('Y-m-d');
        $date_range = $this->getDatesFromRange($date1 . ' 00:00:00', $date2 . ' 23:00:00');

        return view('summary_report_vat.conformity_unit.show_1', [
            'date_range'    => $date_range
        ]);
    }

    public function detail($id1, $id2, Request $request)
    {
        return view('summary_report_vat.conformity_unit.show_2');
    }

    private function getDatesFromRange($date_time_from, $date_time_to)
    {

        // cut hours, because not getting last day when hours of time to is less than hours of time_from
        // see while loop
        $start = Carbon::createFromFormat('Y-m-d', substr($date_time_from, 0, 10));
        $end = Carbon::createFromFormat('Y-m-d', substr($date_time_to, 0, 10));

        $dates = [];

        while ($start->lte($end)) {

            $dates[] = $start->copy()->format('Y-m-d');

            $start->addDay();
        }

        return $dates;
    }
}

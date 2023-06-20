<?php

namespace App\Http\Controllers\SummaryReportVAT;

use App\Center\GridCenter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\LacakBsc01;
use App\Model\RencanaKerja;
use App\Model\RencanaKerjaSummary;
use App\Model\ReportConformity;
use App\Model\VReportRencanaKerja2;
use App\Transformer\LacakBsc01Transformer;
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

        $report_conformities = ReportConformity::paginate(10);
        
        return view('summary_report_vat.conformity_unit.index', [
            'date_range'    => $date_range,
            'list_pg'       => $list_pg,
            'pg'            => $request->pg,
            'list_unit'     => $list_unit,
            'unit'          => $request->unit,
            'report_conformities' => $report_conformities
        ]); 
    }

    public function show(Request $request, $id)
    {
        $report_conformity = ReportConformity::find($id);
        $report_conformities = ReportConformity::where('pg', $report_conformity->pg)
            ->where('unit', $report_conformity->unit)
            ->get();

        $date_range = array_unique($report_conformities->pluck('tanggal')->toArray());

        $report_conformities = $report_conformities->where('tanggal', $request->date);

        $rencana_kerja = RencanaKerja::where('tgl', $request->date)
            ->whereIn('lokasi_kode', array_column($report_conformities->toArray(), 'lokasi'))
            ->get();

        return view('summary_report_vat.conformity_unit.show_1', [
            'date_range'    => $date_range,
            'report_conformity' => $report_conformity,
            'report_conformities' => $report_conformities,
            'rencana_kerja' => $rencana_kerja
        ]);
    }

    public function detail(Request $request, $id)
    {

        $report_conformity = ReportConformity::find($id);
        $report_conformities = ReportConformity::where('pg', $report_conformity->pg)
            ->where('unit', $report_conformity->unit)
            ->get();

        $report_conformities = $report_conformities->where('tanggal', $request->date);

        $rencana_kerja = RencanaKerja::where('unit_label', $report_conformity->unit)
            ->where('tgl', $report_conformity->tanggal)
            ->where('lokasi_kode', $report_conformity->lokasi)
            ->first();

        $list_rrk = VReportRencanaKerja2::where('rencana_kerja_id', $rencana_kerja->id)->get()->toArray();

        $list_rks = RencanaKerjaSummary::where('rk_id', $rencana_kerja->id)->get();
        $header = [];

        foreach ($list_rks as $rks) {
            if ($rks->ritase == 999) {
                $header[$rks->parameter_id] = $rks->parameter_nama;
            }
        }

        return view('summary_report_vat.conformity_unit.show_2', [
            'report_conformity' => $report_conformity,
            'report_conformities' => $report_conformities,
            'rencana_kerja' => $rencana_kerja,
            'list_rrk' => $list_rrk,
            'header' => $header
        ]);
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

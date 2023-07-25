<table class="table table-hover table-bordered rounded" width="100%" id="table-summary">
    <thead>
        <tr>
            <th rowspan="2">Shift</th>
            <th rowspan="2">Total Luasan (Ha)</th>
            <th rowspan="2">Total Overlapping (Ha)</th>
            <th colspan="5">Speed</th>
            <th colspan="5">Wing Kiri</th>
            <th colspan="5">Wing Kanan</th>
            <th colspan="3">Golden Time</th>
            <th colspan="4">Suhu</th>
        </tr>
        <tr>
            <th>Standar</th>
            <th>Dibawah Standar (%)</th>
            <th>Standar (%)</th>
            <th>Diatas Standar (%)</th>
            <th>Average (Km / h)</th>

            <th>Standar</th>
            <th>Dibawah Standar (%)</th>
            <th>Standar (%)</th>
            <th>Diatas Standar (%)</th>
            <th>Average (cm)</th>
            
            <th>Standar</th>
            <th>Dibawah Standar (%)</th>
            <th>Standar (%)</th>
            <th>Diatas Standar (%)</th>
            <th>Average (cm)</th>
            
            <th>Standar</th>
            <th>Tidak Standar (%)</th>
            <th>Standar (%)</th>

            <th>Standar</th>
            <th>Tidak Standar (%)</th>
            <th>Standar (%)</th>
            <th>Average (C)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ $report_conformity->shift }}</td>
            <td class="text-center">{{ $report_conformity->total_spraying != 0 ? round($report_conformity->total_spraying/10000,2):0 }}</td>
            <td class="text-center">{{ $report_conformity->total_overlaping != 0 ? round($report_conformity->total_overlaping/10000,2):0 }}</td>
            <td class="text-center">
                {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 1)
                        ->first()
                        ->range_1
                }} - {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 1)
                        ->first()
                        ->range_2
                }} Km / h
            </td>
            <td class="text-center">{{ $report_conformity->speed_dibawah_standar }}%</td>
            <td class="text-center">{{ $report_conformity->speed_standar }}%</td>
            <td class="text-center">{{ $report_conformity->speed_diatas_standar }}%</td>
            <td class="text-center">{{ $report_conformity->avg_speed }} Km / h</td>
            
            <td class="text-center">
                {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 4)
                        ->first()
                        ->range_1
                }} - {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 4)
                        ->first()
                        ->range_2
                }} cm
            </td>
            @if ($report_conformity->avg_wing_kiri > 2)
                <td class="text-center">{{ $report_conformity->wing_kiri_dibawah_standar }}%</td>
                <td class="text-center">{{ $report_conformity->wing_kiri_standar }}%</td>
                <td class="text-center">{{ $report_conformity->wing_kiri_diatas_standar }}%</td>
                <td class="text-center">{{ $report_conformity->avg_wing_kiri }} cm</td>    
            @else
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
            @endif
            

            <td class="text-center">
                {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 5)
                        ->first()
                        ->range_1
                }} - {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 5)
                        ->first()
                        ->range_2
                }} cm
            </td>

            @if ($report_conformity->avg_wing_kanan > 2)
                <td class="text-center">{{ $report_conformity->wing_kanan_dibawah_standar }}%</td>
                <td class="text-center">{{ $report_conformity->wing_kanan_standar }}%</td>
                <td class="text-center">{{ $report_conformity->wing_kanan_diatas_standar }}%</td>
                <td class="text-center">{{ $report_conformity->avg_wing_kanan }} cm</td>    
            @else
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
                <td class="text-center">N/A</td>
            @endif
            
            <td class="text-center">
                {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 2)
                        ->first()
                        ->range_1
                }} - {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 2)
                        ->first()
                        ->range_2
                }} 
            </td>
            <td class="text-center">{{ $report_conformity->goldentime_tidak_standar }}%</td>
            <td class="text-center">{{ $report_conformity->goldentime_standar}}%</td>

            <td class="text-center">
                @if ($explodeRk != 'Forcing')
                    -
                @else
                {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 6)
                        ->first()
                        ->range_1
                }} - {{ 
                    @$report_param_standard->reportParameterStandarDetails
                        ->where('report_parameter_id', 6)
                        ->first()
                        ->range_2
                }} C
                @endif
            </td>
            @if ($avgRRK > 2)
                <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': $report_conformity->suhu_standar.'%'}}</td>
                <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': $report_conformity->suhu_tidak_standar.'%'}}</td>
                <td class="text-center">{{ round($avgRRK,2).' C'}}</td>                                        
            @else
                <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': 'N/A'}}</td>
                <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': 'N/A'}}</td>
                <td class="text-center">{{ 'N/A'}}</td>
            @endif
            
        </tr>
    </tbody>
</table>
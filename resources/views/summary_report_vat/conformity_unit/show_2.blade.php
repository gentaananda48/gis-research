@extends('base_theme')

@section("style")
    {!! Html::script('AdminLTE-2.4.18/bower_components/chart.js/Chart.js') !!}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.4.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
        .table-responsive .bootgrid-table th, .table-responsive .bootgrid-table td {
            white-space: nowrap !important;
        }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            /* padding: 15px; */
            font-size: 14px;
            vertical-align: middle;
        }
        .table>thead>tr>th {
            text-align: center;
            background-color: #01924C;
            color: #fff;
        }
        .table>tbody>tr>td {
            color: #01924C;
        }
        .dropdown-item {
            display: block;
            width: 100%;
            padding: 0.25rem 1.5rem;
            clear: both;
            font-weight: 400;
            color: #212529;
            text-align: inherit;
            white-space: nowrap;
            background-color: transparent;
            border: 0;
        }
        .bg-1 {
            background-color: rgba(1, 146, 76, 0.6) !important;
            color: #00512A !important;
        }
        .bg-2 {
            background-color: rgba(1, 146, 76, 0.2) !important;
            color: #00512A !important;
        }
        .bg-3 {
            background-color: #F70404 !important;
            color: #fff !important;
        }
        #table-detail td{
            padding: 0px;
            border: 0px;
        }

        @media print {
            body {-webkit-print-color-adjust: exact;}
            
            .hidden-print {
                display: none !important;
            }
            
            .clr-std{
                background-color: #08b160 !important;
                print-color-adjust: exact;
            }
            .clr-btm-std{
                background-color: red !important;
                print-color-adjust: exact;
            }
            .clr-up-std{
                background-color: #f97c22 !important;
                print-color-adjust: exact;
            }

            td.bg-lightgreen {
            background-color: rgba(1, 146, 76, 0.6) !important;
            color: #00512A !important;
            }

            td.bg-green {
                background-color: rgba(1, 146, 76, 0.2) !important;
                color: #00512A !important;
            }

            td.bg-red {
                background-color: #F70404 !important;
                color: #fff !important;
            }

            td.bg-yellow {
                background-color: #F70404 !important;
                color: #fff !important;
            }

            th {
                text-align: center;
                background-color: #01924C;
                color: #fff;
            }
        }

        @page {
            margin: 0cm;
            size: A3 landscape;
        }

        .centered {
        text-align: center;
        }

        .spinner.loading {
        padding: 50px;
        text-align: center;
        }

        .loading-text {
        width: 90px;
        position: absolute;
        top: calc(90% - 25px);
        left: calc(50% - 60px);
        text-align: center;
        }

        .spinner.loading:before {
        content: "";
        height: 90px;
        width: 90px;
        margin: -15px auto auto -15px;
        position: absolute;
        top: calc(90% - 45px);
        left: calc(50% - 45px);
        border-width: 8px;
        border-style: solid;
        border-color: #01924C #ccc #ccc;
        border-radius: 100%;
        animation: rotation .7s infinite linear;
        }

        @keyframes rotation {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(359deg);
        }
        }
    </style>
@stop

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
				    <div id="map" style="width: 100%; height: 500px;"></div>
                    <div style="padding-bottom: 1rem; display:flex; justify-content: star; align-items:center">
                        <div style="display: flex;">
                            <small style="display: flex;margin-top: 10px;"><span class="label label-default map-wing-on" style="background-color: #00FF00;width: 100px;height: 5px;margin: 5px;">&nbsp;</span> Sayap On Kanan & Kiri</small> &nbsp;
                            <small style="display: flex;margin-top: 10px;"><span class="label label-default map-right-on" style="background-color: #FFA500;width: 100px;height: 5px;margin: 5px;">&nbsp;</span> Sayap On Kanan</small> &nbsp;
                            <small style="display: flex;margin-top: 10px;"><span class="label label-default map-left-on" style="background-color: #FFFF00;width: 100px;height: 5px;margin: 5px;">&nbsp;</span> Sayap On Kiri</small>
                            <small style="display: flex;margin-top: 10px;"><span class="label label-default map-wing-off" style="background-color: #FF0000;width: 100px;height: 5px;margin: 5px;">&nbsp;</span> Sayap OFF Kanan & Kiri</small>
                            <small style="display: flex;margin-top: 10px;"><span class="label label-default map-overlapping" style="background-color: #00bbf0;width: 100px;height: 5px;margin: 5px;">&nbsp;</span> Overlapping</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3><strong>{{ $rk->unit_label }}</strong></h3>
                </div>
                <div class="box-body" style="padding-left: 36px; padding-right: 36px;">
                    <table class="table" width="100%" id="table-detail">
                        <tr>
                            <td width="25%"><h4>JENIS APLIKASI</h4></td>
                            <td width="25%"><h4>{{ $rk->aktivitas_nama }}</h4></td>
                            <td width="25%"><h4>LOKASI</h4></td>
                            <td width="25%"><h4>{{ $rk->lokasi_kode }}</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>LUAS NETTO</h4></td>
                            <td width="25%"><h4>{{ $rk->lokasi_lsnetto }} Ha</h4></td>
                            <td width="25%"><h4>LUAS BRUTO</h4></td>
                            <td width="25%"><h4>{{ $rk->lokasi_lsbruto }} Ha</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>NOZZLE</h4></td>
                            <td width="25%"><h4>{{ $rk->nozzle_nama }}</h4></td>
                            <td width="25%"><h4>UNIT</h4></td>
                            <td width="25%"><h4>{{ $rk->unit_label }}</h4></td>
                        </tr>
                        
                        <tr>
                            <td width="25%"><h4>TANGGAL</h4></td>
                            <td width="25%"><h4>{{ $new_date['date'] }}</h4></td>
                            <td width="25%"><h4>VOLUME AIR</h4></td>
                            <td width="25%"><h4>{{ $rk->volume }}</h4></td>
                        </tr>

                        <tr>
                            <td width="25%"><h4>JAM MULAI</h4></td>
                            <td width="25%"><h4>{{ $new_date['jam_mulai'] }}</h4></td>
                            <td width="25%"><h4>SUHU</h4></td>
                            <td width="25%"><h4>{{ $report_conformity->suhu_avg ?? 'N/A' }}</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>JAM SELESAI</h4></td>
                            <td width="25%"><h4>{{ $new_date['jam_akhir'] }}</h4></td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header">
                    <div class="btn-group hidden-print">
                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Export <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                          <li><a href="{{ route('summary.conformity_unit.export_detail',$report_conformity->id) }}">Excel</a></li>
                          {{-- <li><a href="javascript:void(0)" class="btn-print">PDF</a></li> --}}
                        </ul>
                    </div>

                    <h3 class="text-center"><strong>Summary</strong></h3>
                </div>
                
                <div class="box-body">
                    <div class="table-responsive">
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
                                    @if ($report_conformity->suhu_avg > 2)
                                        <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': $report_conformity->suhu_standar.'%'}}</td>
                                        <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': $report_conformity->suhu_tidak_standar.'%'}}</td>
                                        <td class="text-center">{{ $report_conformity->suhu_avg !== null ? round($report_conformity->suhu_avg, 2).' C' : 'N/A' }}</td>
                                    @else
                                        <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': 'N/A'}}</td>
                                        <td class="text-center">{{ $explodeRk != 'Forcing' ? '-': 'N/A'}}</td>
                                        <td class="text-center">{{ 'N/A'}}</td>
                                    @endif
                                    
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            
                <hr>

                <div class="box-header text-center">
                    <h3 style="margin-bottom: 0px;"><strong>Conformity Unit {{ $report_conformity->pg }} - {{ $report_conformity->unit }}</strong></h3>
                </div>
                <div class="box-body">
                    <div style="padding-bottom: 1rem; display:flex; justify-content: end; align-items:center">
                        <div>
                            <small><span class="label label-default clr-std" style="background-color: #08b160">&nbsp;</span> Standar</small> &nbsp;
                            <small><span class="label label-default clr-btm-std" style="background-color: red">&nbsp;</span> Dibawah Standar</small> &nbsp;
                            <small><span class="label label-default clr-up-std" style="background-color: #f97c22">&nbsp;</span> Diatas Standar</small>
                        </div>
                    </div>
                    <div >
                        <table class="table table-hover table-bordered rounded" width="100%">
                            <thead>
                                <tr>
                                    <th>Speed</th>
                                    <th>Wing Kiri</th>
                                    <th>Wing Kanan</th>
                                    <th>Golden Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="speed_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td>
                                    <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="wing_kiri_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td>
                                    <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="wing_kanan_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td>
                                    <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="golden_time_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <hr>

                <div class="load-ritase">
                    <div class="box-header text-center">
                        <h3><strong>Detail Per Ritase</strong></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-hover table-bordered rounded" width="100%" id="table-down">
                            <thead>
                                <tr>
                                    <th>Ritase</th>
                                    <th>Kecepatan Operasi</th>
                                    <th>Golden Time</th>
                                    <th>Waktu Spray</th>
                                    <th>Wing Level Kiri</th>
                                    <th>Wing Level Kanan</th>
                                    <th>Suhu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="100">
                                        <div class="centered">
                                            <div id="divSpinner" class="spinner loading">
                                              <div class="loading-text">Loading ...</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="padding: 10px;" class="hidden-print">
                    <a href="{{ url()->previous() }}" class="btn btn-warning"  style="margin: 0px; margin-right: 8px;">Back</a>
                    <a href="{{ route('report.rencana_kerja.playback',$rk->id) }}" class="btn btn-success"> Playback</a>

                    {{-- <button class="btn btn-success btn-sm btn-print" style="margin: 0">Export</button> --}}
                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section("script")
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>
   $(document).ready(function() {
        setTimeout(function() {
            $.ajax({
            type: 'GET',
            url: BASE_URL + '/summary/conformity_ritase/'+{{ $report_conformity->id }},
                success: function(data){
                    $('.load-ritase').html(data.html);
                }
            });
        }, 15000);
        
        $('.btn-print').click(function(){
           window.print();
           return false;
        });

        const reportConformity = JSON.parse('{!! $report_conformity !!}')

        if (reportConformity.speed_standar + reportConformity.speed_dibawah_standar + reportConformity.speed_diatas_standar != 0) {
            pieChart("speed_1", [
            reportConformity.speed_standar.toFixed(2),
            reportConformity.speed_dibawah_standar.toFixed(2),
            reportConformity.speed_diatas_standar.toFixed(2)
            ]);     
        }else{
            $('#speed_1').parent().html('N/A');
        }

        if (reportConformity.avg_wing_kiri > 2) {
            if (reportConformity.wing_kiri_standar + reportConformity.wing_kiri_dibawah_standar + reportConformity.wing_kiri_diatas_standar != 0) {
                pieChart("wing_kiri_1", [
                reportConformity.wing_kiri_standar.toFixed(2),
                reportConformity.wing_kiri_dibawah_standar.toFixed(2),
                reportConformity.wing_kiri_diatas_standar.toFixed(2)
                ]);    
            }else{
                $('#wing_kiri_1').parent().html('N/A');
            }
        }else{
            $('#wing_kiri_1').parent().html('N/A');
        }
        

        if (reportConformity.avg_wing_kanan > 2) {
            if (reportConformity.wing_kanan_standar + reportConformity.wing_kanan_dibawah_standar + reportConformity.wing_kanan_diatas_standar != 0) {
                pieChart("wing_kanan_1", [
                reportConformity.wing_kanan_standar.toFixed(2),
                reportConformity.wing_kanan_dibawah_standar.toFixed(2),
                reportConformity.wing_kanan_diatas_standar.toFixed(2)
                ]);   
            }else{
                $('#wing_kanan_1').parent().html('N/A');
            }
        }else{
            $('#wing_kanan_1').parent().html('N/A');
        }

        if (reportConformity.goldentime_standar + reportConformity.goldentime_tidak_standar != 0) {
            pieChart("golden_time_1", [
            reportConformity.goldentime_standar.toFixed(2),
            reportConformity.goldentime_tidak_standar.toFixed(2),
            ], 'golden_time');  
        }else{
            $('#golden_time_1').parent().html('N/A');
        }
        // pieChart("waktu_spray_1", [
        //     reportConformity.spray_standar,
        //     reportConformity.spray_tidak_standar,
        // ], 'waktu_spray');

        $('.date-slider').slick({
            dots: false,
            infinite: false,
            speed: 300,
            slidesToShow: 8,
            slidesToScroll: 8,
            arrows: true,
            prevArrow:"<button type='button' class='slick-prev'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
            nextArrow:"<button type='button' class='slick-next'><i class='fa fa-angle-right' aria-hidden='true'></i></button>"
        });

        $('.date-range').on('click', function() {
            let data = $(this).data('date');
        
            window.location.href = '{{route("summary.conformity_unit.show", $report_conformity->id)}}?date='+data
        })
    });

    function pieChart(el, yValues, type) {
        var xValues = ["Standar", "Dibawah Standar", "Diatas Standar"];

        if(type == 'golden_time' || type == 'waktu_spray') {
            xValues = ["Standar", "Tidak Standar"]
        }

        //ref: public/js/constants.js
        var barColors = [
            CHART_GREEN,
            // CHART_RED,
            'rgba(232, 33, 53)',
            CHART_YELLOW,
        ];

        var ctx = el; // element id

        new Chart(ctx, {
            type: "pie",
            data: {
                labels: xValues,
                datasets: [{
                backgroundColor: barColors,
                data: yValues
                }]
            },
            options: {
                title: {
                display: false
                },
                legend: {
                    display: false,
                    position: 'bottom'
                },
                
                plugins: {
                    datalabels: {
                        formatter: (value, ctx) => {
                            if (value > 0) {
                                return value + '%';
                            } else {
                                value = "";
                                return value;
                            }
                        },
                        color: '#fff',
                        display: 'auto',
                        anchor: 'end',
                        align: 'start',
                        offset: -1,
                        clamp: true,
                        font: {
                            size: 12
                        },
                        clip: 'auto'
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                        var datasetLabel = '';
                        var label = data.labels[tooltipItem.index];
                        return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                        }
                    }
                }
            },
        });
    }

</script>

<script>
	var map;
	var marker;
	var poly;
	var lacak = {!! $list_lacak !!};
	var lokasi = {!! $list_lokasi !!};
	var timestamp_jam_mulai = "{!! $timestamp_jam_mulai ? $timestamp_jam_mulai:'-' !!}";
	var timestamp_jam_selesai = "{!! $timestamp_jam_selesai ? $timestamp_jam_selesai:'-' !!}";
	var i = 0;

	function initMap() {
		map = new google.maps.Map(document.getElementById("map"), {
		    zoom: 17,
		    center: {lng: lokasi[0]['koordinat'][0]['lng'], lat: lokasi[0]['koordinat'][0]['lat']},
		    mapTypeId: "satellite",
		});

        // overlapping
        // var overlapping = {!! $list_overlapping !!};
        // map2 = new google.maps.Map(document.getElementById("map2"), {
		//     zoom: 17,
		//     center: {lng: lokasi[0]['koordinat'][0]['lng'], lat: lokasi[0]['koordinat'][0]['lat']},
		//     mapTypeId: "satellite",
		// });
        // var infowindow = new google.maps.InfoWindow();

        // var marker2;

        // for (var i = 0, len = overlapping.length; i < len; i += 1) {  
        //     marker2 = new google.maps.Marker({
        //         position: new google.maps.LatLng(overlapping[i].position_latitude, overlapping[i].position_longitude),
        //         map: map2
        //     });
            
        //     google.maps.event.addListener(marker2, 'click', (function(marker2, i) {
        //         return function() {
        //         infowindow.setContent(overlapping[i].position_latitude+'<br>'+overlapping[i].position_longitude);
        //         infowindow.open(map, marker2);
        //         }
        //     })(marker2, i));
        //     var position = new google.maps.LatLng(overlapping[i].position_latitude, overlapping[i].position_longitude);
        //     const overlappingPath = new google.maps.Polyline({
        //         path: [new google.maps.LatLng(overlapping[i].position_latitude, overlapping[i].position_longitude), position],
        //         geodesic: true,
        //         strokeColor: "#FF0000",
        //         strokeOpacity: 1.0,
        //         strokeWeight: 2,
        //     });

        //     overlappingPath.setMap(map2);

        // }
        // overlapping

		google.maps.Polygon.prototype.my_getBounds=function(){
		    var bounds = new google.maps.LatLngBounds()
		    this.getPath().forEach(function(element,index){bounds.extend(element)})
		    return bounds
		}

        if (lacak.length > 0) {
            marker = new google.maps.Marker({
                position: {lng: lacak[0].position_longitude, lat: lacak[0].position_latitude},
                //label: 'TEST',
                map: map,
                //animation: google.maps.Animation.DROP,
                icon: {
                    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                    fillColor: '#04F8E5',
                    fillOpacity: 1,
                    strokeWeight: 1,
                    strokeColor: '#04F8E5',
                    scale: 5,
                    rotation: lacak[0].position_direction,
                    anchor: new google.maps.Point(0, 5),
                }
            });   
        }

		var list_lokasi = {!! $list_lokasi !!} || []
	  	list_lokasi.forEach(function(lokasi) {
		  	const polygon = new google.maps.Polygon({
			    paths: lokasi.koordinat,
			    strokeColor: '#964B00',
			    strokeOpacity: 0.9,
			    strokeWeight: 2,
			    fillColor: '#964B00',
			    fillOpacity: 0.4
		  	});
	    	polygon.infoWindow = new google.maps.InfoWindow({
	      		content: lokasi.nama
	    	});
		  	polygon.setMap(map);
		  	map.setCenter(polygon.my_getBounds().getCenter());
		});
        if (lacak.length > 0) {
            for (var i = 0, len = lacak.length; i < len; i += 1) {
                // if(timestamp_jam_mulai > lacak[i].timestamp || lacak[i].timestamp > timestamp_jam_selesai) {
                //     continue;
                // }
                var icon = marker.getIcon();
                icon.rotation = lacak[i].position_direction;
                marker.setIcon(icon);
                var position = new google.maps.LatLng(lacak[i].position_latitude, lacak[i].position_longitude);
                // var position2 = new google.maps.LatLng(overlapping[i].position_latitude, overlapping[i].position_longitude);
                marker.setPosition(position);
                // marker.setPosition(position2);
                if(i>0){
                        // const overlappingPath = new google.maps.Polyline({
                        //     path: [new google.maps.LatLng(overlapping[i-1].position_latitude, overlapping[i-1].position_longitude), position],
                        //     geodesic: true,
                        //     strokeColor: "#f70776",
                        //     strokeOpacity: 0.3,
                        //     strokeWeight: 7,
                        //     zIndex: 999999
                        // });

                        // overlappingPath.setMap(map);
                        if (lacak[i-1].is_overlapping == 1) {
                            // var poly = new google.maps.Polyline({
                            //     path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
                            //     geodesic: true,
                            //     strokeColor: "#00bbf0",
                            //     strokeOpacity: 0.3,
                            //     strokeWeight: 7,
                            //     zIndex: 999999,
                            // });
                            // poly.setMap(map);
                            new google.maps.Circle({
                                strokeColor: '#00bbf0',
                                strokeOpacity: 0.9,
                                strokeWeight: 7,
                                fillColor: '#00bbf0',
                                fillOpacity: 0.9,
                                map,
                                center: { lat: lacak[i-1].position_latitude, lng: lacak[i-1].position_longitude },
                                radius: 1,
                                zIndex: 999999
                            });
                        } else if(lacak[i-1].pump_switch_main == 1 && (lacak[i-1].pump_switch_right==1 || lacak[i-1].pump_switch_left==1)) {
                            var strokeColor = lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==1 ? "#00FF00" : lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==0 ? "#FFA500" : "#FFFF00";
                            var strokeWeight = lacak[i-1].pump_switch_right==1 && lacak[i-1].pump_switch_left==1 ? 12 : 7;
                            // var poly = new google.maps.Polyline({
                            //     path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
                            //     geodesic: true,
                            //     strokeColor: strokeColor,
                            //     strokeOpacity: 0.5,
                            //     strokeWeight: strokeWeight,
                            //     zIndex: 99999,
                            // });
                            // poly.setMap(map);
                            new google.maps.Circle({
                                strokeColor: strokeColor,
                                strokeOpacity: 0.5,
                                strokeWeight: strokeWeight,
                                fillColor: strokeColor,
                                fillOpacity: 0.35,
                                map,
                                center: { lat: lacak[i-1].position_latitude, lng: lacak[i-1].position_longitude },
                                radius: 0.3,
                                zIndex: 99999
                            });
                        } else {
                            // var poly = new google.maps.Polyline({
                            //     path: [new google.maps.LatLng(lacak[i-1].position_latitude, lacak[i-1].position_longitude), position],
                            //     geodesic: true,
                            //     strokeColor: "#FF0000",
                            //     strokeOpacity: 0.5,
                            //     strokeWeight: 3,
                            //     zIndex: 99999,
                            // });
                            // poly.setMap(map);
                            new google.maps.Circle({
                                strokeColor: '#FF0000',
                                strokeOpacity: 0.5,
                                strokeWeight: 3,
                                fillColor: '#FF0000',
                                fillOpacity: 0.35,
                                map,
                                center: { lat: lacak[i-1].position_latitude, lng: lacak[i-1].position_longitude },
                                radius: 0.3,
                                zIndex: 99999
                            });
                        }
                    }
            }   
        }
	}
</script>
 <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBALLhhVF_c4wQ1CdlsZaDCaCD0ekaJn3Q&callback=initMap&libraries=&v=weekly"
      async
    ></script>
@stop
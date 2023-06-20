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
    </style>
@stop

@section('content')
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3><strong>{{ $rencana_kerja->unit_label }}</strong></h3>
                </div>
                <div class="box-body" style="padding-left: 36px; padding-right: 36px;">
                    <table class="table" width="100%" id="table-detail">
                        <tr>
                            <td width="25%"><h4>JENIS APLIKASI</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->aktivitas_nama }}</h4></td>
                            <td width="25%"><h4>LOKASI</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->lokasi_kode }}</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>LUAS NETTO</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->lokasi_lsnetto }} Ha</h4></td>
                            <td width="25%"><h4>LUAS BRUTO</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->lokasi_lsbruto }} Ha</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>NOZZLE</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->nozzle_nama }}</h4></td>
                            <td width="25%"><h4>UNIT</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->unit_label }}</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>JAM MULAI</h4></td>
                            <td width="25%"><h4>{{ date('Y-m-d | H:i:s', strtotime($rencana_kerja->jam_mulai)) }}</h4></td>
                            <td width="25%"><h4>VOLUME AIR</h4></td>
                            <td width="25%"><h4>{{ $rencana_kerja->volume }}</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>JAM SELESAI</h4></td>
                            <td width="25%"><h4>{{ date('Y-m-d | H:i:s', strtotime($rencana_kerja->jam_selesai)) }}</h4></td>
                            <td width="25%"><h4>SUHU</h4></td>
                            <td width="25%"><h4>-</h4></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3><strong>Summary</strong></h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered rounded" width="100%" id="table-summary">
                            <thead>
                                <tr>
                                    <th rowspan="2">Shift</th>
                                    <th colspan="5">Speed</th>
                                    <th colspan="5">Wing Kanan</th>
                                    <th colspan="5">Wing Kiri</th>
                                    <th colspan="5">Suhu</th>
                                </tr>
                                <tr>
                                    <th>Standar</th>
                                    <th>Dibawah Standar (%)</th>
                                    <th>Standar (%)</th>
                                    <th>Diatas Standar (%)</th>
                                    <th>Average (%)</th>
                                    <th>Standar</th>
                                    <th>Dibawah Standar (%)</th>
                                    <th>Standar (%)</th>
                                    <th>Diatas Standar (%)</th>
                                    <th>Average (%)</th>
                                    <th>Standar</th>
                                    <th>Dibawah Standar (%)</th>
                                    <th>Standar (%)</th>
                                    <th>Diatas Standar (%)</th>
                                    <th>Average (%)</th>
                                    <th>Standar</th>
                                    <th>Dibawah Standar (%)</th>
                                    <th>Standar (%)</th>
                                    <th>Diatas Standar (%)</th>
                                    <th>Average (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">{{ $report_conformity->shift }}</td>
                                    <td class="text-center">N/A m/s</td>
                                    <td class="text-center">{{ $report_conformity->speed_dibawah_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->speed_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->speed_diatas_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->avg_speed }}%</td>
                                    <td class="text-center">N/A cm</td>
                                    <td class="text-center">{{ $report_conformity->wing_kanan_dibawah_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->wing_kanan_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->wing_kanan_diatas_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->avg_wing_kanan }}%</td>
                                    <td class="text-center">N/A cm</td>
                                    <td class="text-center">{{ $report_conformity->wing_kiri_dibawah_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->wing_kiri_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->wing_kiri_diatas_standar }}%</td>
                                    <td class="text-center">{{ $report_conformity->avg_wing_kiri }}%</td>
                                    <td class="text-center">N/A m/s</td>
                                    <td class="text-center">N/A%</td>
                                    <td class="text-center">N/A%</td>
                                    <td class="text-center">N/A%</td>
                                    <td class="text-center">N/A%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            
                <hr>

                <div class="box-header text-center">
                    <h3 style="margin-bottom: 0px;"><strong>Conformity Unit PG1 - BSC 11</strong></h3>
                </div>
                <div class="box-body">
                    <div style="padding-bottom: 1rem; display:flex; justify-content: end; align-items:center">
                        <div>
                            <small><span class="label label-default" style="background-color: #08b160">&nbsp;</span> Standar</small> &nbsp;
                            <small><span class="label label-default" style="background-color: red">&nbsp;</span> Dibawah Standar</small> &nbsp;
                            <small><span class="label label-default" style="background-color: #f97c22">&nbsp;</span> Diatas Standar</small>
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
                                    <th>Waktu Spray</th>
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
                                    <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="waktu_spray_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <hr>

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
                            @foreach($list_rrk as $v)
                                    <tr>
                                            <td>{{ $v['ritase'] }}</td>
                                            @foreach($header as $k2 => $v2)
                                                    @if ($k2 == 4 || $k2 == 5)
                                                            <th>{{ doubleval($v['parameter_'.$k2]) <= 2 ? 'N/A' : $v['parameter_'.$k2] }}</th>
                                                    @else
                                                            <th>{{ $v['parameter_'.$k2] }}</th>
                                                    @endif
                                            @endforeach
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div style="padding-top: 1rem;">
                        <a href="{{ route('summary.conformity_unit') }}" class="btn btn-warning btn-sm"  style="margin: 0px; margin-right: 8px;">Back</a>

                        <button class="btn btn-success btn-sm" style="margin: 0">Export</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section("script")

<script>
   $(document).ready(function() {
        const reportConformity = JSON.parse('{!! $report_conformity !!}')

        pieChart("speed_1", [
            reportConformity.speed_standar,
            reportConformity.speed_dibawah_standar,
            reportConformity.speed_diatas_standar
        ]);
        pieChart("wing_kiri_1", [
            reportConformity.wing_kiri_standar,
            reportConformity.wing_kiri_dibawah_standar,
            reportConformity.wing_kiri_diatas_standar
        ]);
        pieChart("wing_kanan_1", [
            reportConformity.wing_kanan_standar,
            reportConformity.wing_kanan_dibawah_standar,
            reportConformity.wing_kanan_diatas_standar
        ])
        pieChart("golden_time_1", [
            reportConformity.goldentime_standar,
            reportConformity.goldentime_tidak_standar,
        ], 'golden_time');
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
            CHART_RED,
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
                            let datasets = ctx.chart.data.datasets;
                            if (datasets.indexOf(ctx.dataset) === datasets.length - 1) {
                            let sum = datasets[0].data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value / sum) * 100) + '%';
                            return percentage;
                            } else {
                            return percentage;
                            }
                        },
                        color: '#fff',
                    }
                }
            },
        });
    }

</script>
@stop
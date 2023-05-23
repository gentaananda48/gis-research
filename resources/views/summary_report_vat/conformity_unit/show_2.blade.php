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
                    <h3><strong>BSC - 11</strong></h3>
                </div>
                <div class="box-body" style="padding-left: 36px; padding-right: 36px;">
                    <table class="table" width="100%" id="table-detail">
                        <tr>
                            <td width="25%"><h4>JENIS APLIKASI</h4></td>
                            <td width="25%"><h4>Insektisida 2</h4></td>
                            <td width="25%"><h4>LOKASI</h4></td>
                            <td width="25%"><h4>078D2</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>LUAST NETTO</h4></td>
                            <td width="25%"><h4>6.35 Ha</h4></td>
                            <td width="25%"><h4>LUAS BRUTO</h4></td>
                            <td width="25%"><h4>7.86 Ha</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>NOZZLE</h4></td>
                            <td width="25%"><h4>Flood Jet KSS-60</h4></td>
                            <td width="25%"><h4>UNIT</h4></td>
                            <td width="25%"><h4>BCS - 11</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>JAM MULAI</h4></td>
                            <td width="25%"><h4>2023-04-03 | 18:27:24</h4></td>
                            <td width="25%"><h4>VOLUME AIR</h4></td>
                            <td width="25%"><h4>3000</h4></td>
                        </tr>
                        <tr>
                            <td width="25%"><h4>JAM SELESAI</h4></td>
                            <td width="25%"><h4>2023-04-03 | 18:27:24</h4></td>
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
                                    <td class="text-center">Malam</td>
                                    <td class="text-center">85 m/s</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">85 cm</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">85 cm</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">85 m/s</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">15%</td>
                                    <td class="text-center">20%</td>
                                    <td class="text-center">20%</td>
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
                            <small><span class="label label-default" style="background-color: #ffd95a">&nbsp;</span> Dibawah Standar</small> &nbsp;
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
                                <td>8.20</td>
                                <td>18:27:24</td>
                                <td>13.68</td>
                                <td>181.21</td>
                                <td>136.65</td>
                                <td>23.11</td>
                            </tr>
                            <tr>
                                <td>8.20</td>
                                <td>18:27:24</td>
                                <td>13.68</td>
                                <td>181.21</td>
                                <td>136.65</td>
                                <td>23.11</td>
                            </tr>
                            <tr>
                                <td>8.20</td>
                                <td>18:27:24</td>
                                <td>13.68</td>
                                <td>181.21</td>
                                <td>136.65</td>
                                <td>23.11</td>
                            </tr>
                            <tr>
                                <td>8.20</td>
                                <td>18:27:24</td>
                                <td>13.68</td>
                                <td>181.21</td>
                                <td>136.65</td>
                                <td>23.11</td>
                            </tr>
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
        for (let index = 1; index < 2; index++) {
            pieChart("speed_"+index);
            pieChart("wing_kiri_"+index);
            pieChart("wing_kanan_"+index);
            pieChart("golden_time_"+index);
            pieChart("waktu_spray_"+index);
        }
    });

    function pieChart(el) {
        var xValues = ["Standar", "Dibawah Standar", "Diatas Standar"];
        var yValues = [generateRandom(), generateRandom(), generateRandom()];
        var barColors = [
            "#08b160",
            "#ffd95a",
            "#f97c22",
        ];

        var ctx = document.getElementById(el); // node
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

    function generateRandom(maxLimit = 100){
        let rand = Math.random() * maxLimit;

        rand = Math.floor(rand); // 99

        return rand;
    }
</script>
@stop
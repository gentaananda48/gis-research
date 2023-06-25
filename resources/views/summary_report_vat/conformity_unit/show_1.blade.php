@extends('base_theme')

@section("style")
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
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
        .bg-lightgreen {
            background-color: rgba(1, 146, 76, 0.6) !important;
            color: #00512A !important;
        }
        .bg-green {
            background-color: rgba(1, 146, 76, 0.2) !important;
            color: #00512A !important;
        }
        .bg-red {
            background-color: #F70404 !important;
            color: #fff !important;
        }

        .bg-yellow {
            background-color: #F70404 !important;
            color: #fff !important;
        }

        .slick-list {
            width: 90%;
            margin: 0 auto;
            padding-bottom: 20px;
            
        }
        .slick-list .slick-slide {
            text-align: center;
            margin: 0px 4px;
            height: 32px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #01924C;
            cursor: pointer;
        }

        .slick-selected{
            background-color: #01924C !important;
            color: #fff !important;
        }
        .slick-list .slick-slide:nth-child(even) {
            background-color: #F2FAF6;
        }
        .slick-list .slick-slide:nth-child(odd) {
            background-color: #F2FAF6;
        }
        .slick-arrow {
            z-index: 1;
            width: 32px;
            height: 32px;
        }

        .slick-arrow:before {
            /* font-size: 30px; */
        }
        .slick-next::before {
            font-family: FontAwesome;
            content: "\f105";
        }
        .slick-prev::before {
            font-family: FontAwesome;
            content: "\f104";
        }
        .slick-next {
            right: 0;
            top: 6px;
            background-color: #01924C;
            color: #fff;
        }
        .slick-prev {
            left: 0;
            top: 6px;
            background-color: #01924C;
            color: #fff;
        }
        .slick-prev:hover, .slick-prev:focus, .slick-next:hover, .slick-next:focus {
            background-color: #01924C;
            color: #fff;
        }
        .btn-wrap {
            text-align: center;
            width: 100%;
        }
        button {
            border: none;
            /* padding: 10px 20px; */
            margin: 10px;
            font-size: 15px;
            font-weight: 600;
        }
    </style>
@stop

@section('content')
<section class="content">
    
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3 style="margin-bottom: 0px;"><strong>Conformity Unit {{ $report_conformity->pg }} - {{ $report_conformity->unit }}</strong></h3>
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
                                    {{-- <th>Waktu Spray</th> --}}
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
                                    {{-- <td width="200px">
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="waktu_spray_1" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td> --}}
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3><strong>Conformity Unit Per Lokasi</strong></h3>
                </div>
                <div class="box-body">
                    <div class="date-slider">
                        @foreach ($date_range as $date)
                            <div class="date-range {{ request()->date == $date ? 'slick-selected slick-current' : '' }}" data-date="{{ $date }}">{{ date('d/m/Y', strtotime($date)) }}</div>
                        @endforeach
                    </div>

                    <table class="table table-hover table-bordered rounded" width="100%" id="table-down">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th style="width: 100px">PG</th>
                                <th style="width: 100px">Unit</th>
                                <th>On Standar Speed</th>
                                <th>On Standar Wing Kiri</th>
                                <th>On Standar Wing Kanan</th>
                                <th>On Standar Golden Time</th>
                                <th>Lokasi</th>
                                <th>Rencana Kerja</th>
                                <th>Shift</th>
                                <th>Jenis Aplikasi</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report_conformities as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration  }}</td>
                                    <td>{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                                    <td>{{ $item->pg }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td class="{{ $item->getStandardColor($item->speed_standar) }}">{{ $item->speed_standar }}%</td>
                                    <td class="{{ $item->getStandardColor($item->wing_kiri_standar) }}">{{ $item->wing_kiri_standar }}%</td>
                                    <td class="{{ $item->getStandardColor($item->wing_kanan_standar) }}">{{ $item->wing_kanan_standar }}%</td>
                                    <td class="{{ $item->getStandardColor($item->goldentime_standar) }}">{{ $item->goldentime_standar }}%</td>
                                    <td>{{ $item->lokasi }}</td>
                                    <td>{{ $rencana_kerja->count() > 0 ? 'Y' : 'N' }}</td>
                                    <td>{{ $item->shift }}</td>
                                    <td>{{ $item->activity }}</td>
                                    <td class="text-center"><a href="{{ route('summary.conformity_unit.detail', $item->id) }}" class="btn btn-success btn-sm">Detail</a></td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="100" class="text-center">No data available</td>
                            </tr>
                            @endforelse
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
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

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
                            if(value >0 ){
                                let datasets = ctx.chart.data.datasets;
                                if (datasets.indexOf(ctx.dataset) === datasets.length - 1) {
                                let sum = datasets[0].data.reduce((a, b) => a + b, 0);
                                let percentage = Math.round((value / sum) * 100) + '%';
                                return percentage;
                                } else {
                                return percentage;
                                }
                            }else{
                                value = "";
                                return value;
                            }
                        },
                        color: '#fff',
                    }
                }
            },
        });
    }

    $(function () {
        $("[data-widget='collapse']").click();
        $('#date_range').daterangepicker();
    });

    function generateRandom(maxLimit = 100){
        let rand = Math.random() * maxLimit;

        rand = Math.floor(rand); // 99

        return rand;
    }
</script>
@stop
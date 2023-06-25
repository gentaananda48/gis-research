@extends('base_theme')

@section("style")
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
    </style>
    {!! Html::script('AdminLTE-2.4.18/bower_components/chart.js/Chart.js') !!}
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.4.0/dist/chartjs-plugin-datalabels.min.js"></script>
@stop

@section('content')
<section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
              </button>
            </div>
          </div>
          <div class="box-body">
            <form>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="date_range">Tanggal</label>
                    {{ Form::text('date_range', $date_range, array('id' => 'date_range', 'class' => 'form-control', 'autocomplete'=>'off')) }}
                </div> 
                <div class="col-md-4">
                    <label for="pg">PG</label>
                    {{ Form::select('pg[]', $list_pg , $pg, array('class' => 'form-control select2')) }}  
                </div>
    
                <div class="col-md-4">
                    <label for="unit">UNIT</label>
                    {{ Form::select('unit[]', $list_unit , $unit, array('class' => 'form-control select2')) }}  
                </div>
            </div>
            <button type="submit" class="btn btn-success" style="margin-top: 23px;"><i class="fa fa-search"></i></button> 
            <a href="{{ route('summary.conformity_unit') }}" type="submit" class="btn btn-warning" style="margin-top: 23px;">Clear Filter</a> 
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header text-center">
                    <h3><strong>Conformity Unit</strong></h3>
                </div>
                <div class="box-body">
                    <div style="padding-bottom: 1rem; display:flex; justify-content: space-between; align-items:center">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              Export <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a href="#">Excel</a></li>
                              <li><a href="#">PNG</a></li>
                            </ul>
                        </div>

                        <div>
                            <small><span class="label label-default" style="background-color: #08b160">&nbsp;</span> Standar</small> &nbsp;
                            <small><span class="label label-default" style="background-color: red">&nbsp;</span> Dibawah Standar</small> &nbsp;
                            <small><span class="label label-default" style="background-color: #f97c22">&nbsp;</span> Diatas Standar</small>
                        </div>
                    </div>
                    <div >
                        <table class="table table-hover table-bordered" width="100%">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th style="width: 100px;">PG</th>
                                    <th style="width: 100px;">Unit</th>
                                    {{-- <th style="width: 100px;">Lokasi</th> --}}
                                    <th>Speed</th>
                                    <th>Wing Kiri</th>
                                    <th>Wing Kanan</th>
                                    <th>Golden Time</th>
                                    <th>Tanggal</th>
                                    {{-- <th>Waktu Spray</th> --}}
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report_conformities as $key => $report_conformity)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration + ($report_conformities->currentPage() - 1) * $report_conformities->perPage() }}</td>
                                    <td>{{ $report_conformity->pg }}</td>
                                    <td>{{ $report_conformity->unit }}</td>
                                    {{-- <td>{{ $report_conformity->lokasi }}</td> --}}
                                    <td>
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="speed_{{$key}}" width="100" height="100"></canvas>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="wing_kiri_{{$key}}" width="100" height="100"></canvas>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="wing_kanan_{{$key}}" width="100" height="100"></canvas>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="golden_time_{{$key}}" width="100" height="100"></canvas>
                                        </div>
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($report_conformity->tanggal)) }}</td>
                                    {{-- <td>
                                        <div style="display: flex; justify-content: center;">
                                            <canvas id="waktu_spray_{{$key}}" style="width:100%;max-width:100%"></canvas>
                                        </div>
                                    </td> --}}
                                    <td class="text-center">
                                        <a href="{{ route('summary.conformity_unit.show', $report_conformity->id) }}" class="btn btn-success btn-sm">View Detail</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="100" class="text-center">No data available</td>
                                </tr>
                                @endforelse
                                {{-- <tr>
                                    <td class="text-center">2</td>
                                    <td>PG 2</td>
                                    <td>Unit 2</td>
                                    <td>
                                        <canvas id="speed_2" style="width:150px;max-width:150px"></canvas>

                                    </td>
                                    <td>
                                        <canvas id="wing_kiri_2" style="width:150px;max-width:150px"></canvas>
                                    </td>
                                    <td>
                                        <canvas id="wing_kanan_2" style="width:150px;max-width:150px"></canvas>
                                    </td>
                                    <td>
                                        <canvas id="golden_time_2" style="width:150px;max-width:150px"></canvas>
                                    </td>
                                    <td>
                                        <canvas id="waktu_spray_2" style="width:150px;max-width:150px"></canvas>
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-success btn-sm">View Detail</a>
                                    </td>
                                </tr> --}}
                            </tbody>
                        </table>
                        {{$report_conformities->links()}}
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
        const reportConformities = JSON.parse('{!! collect($report_conformities->toArray()["data"]) !!}')

        for (let index = 0; index < reportConformities.length; index++) {
            pieChart("speed_"+index, [
                reportConformities[index].speed_standar.toFixed(2),
                reportConformities[index].speed_dibawah_standar.toFixed(2),
                reportConformities[index].speed_diatas_standar.toFixed(2)
            ]);
            pieChart("wing_kiri_"+index, [
                reportConformities[index].wing_kiri_standar.toFixed(2),
                reportConformities[index].wing_kiri_dibawah_standar.toFixed(2),
                reportConformities[index].wing_kiri_diatas_standar.toFixed(2)
            ]);
            pieChart("wing_kanan_"+index, [
                reportConformities[index].wing_kanan_standar.toFixed(2),
                reportConformities[index].wing_kanan_dibawah_standar.toFixed(2),
                reportConformities[index].wing_kanan_diatas_standar.toFixed(2)
            ])
            pieChart("golden_time_"+index, [
                reportConformities[index].goldentime_standar.toFixed(2),
                reportConformities[index].goldentime_tidak_standar.toFixed(2),
            ], 'golden_time');
            // pieChart("waktu_spray_"+index, [
            //     reportConformities[index].spray_standar,
            //     reportConformities[index].spray_tidak_standar,
            // ], 'waktu_spray');
        }
    });

    function pieChart(el, yValues, type = '') {
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
                maintainAspectRatio: false,
                title: {
                display: false
                },
                legend: {
                    display: false,
                    position: 'bottom'
                },
                plugins: {
                    datalabels: {
                        display: true,
                        formatter: (value, ctx) => {
                            if(value >0 ){
                                return value + ' %'
                            }else{
                                value = "";
                                return value;
                            }
                            
                        },
                        font: {
                            weight: 'bold',
                            size: 8
                        },
                        color: '#000000',
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
        console.log(rand); // say 99.81321410836433

        rand = Math.floor(rand); // 99

        return rand;
    }

</script>{!! Html::script('/js/summary_report_vat/conformity_unit.js') !!}
@stop
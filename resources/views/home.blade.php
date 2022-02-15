@extends('base_theme')
@section('style')
<style>
</style>
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
            <div class="form-group col-md-3">
              <label for="date_range">Tanggal</label>
              {{ Form::text('date_range', $date_range, array('id' => 'date_range', 'class' => 'form-control', 'autocomplete'=>'off')) }}
            </div> 
            <div class="col-md-2">
              <label for="pg">PG</label>
              {{ Form::select('pg[]', $list_pg , $pg, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
            </div> 
            <div class="col-md-3">
              <label for="aktivitas">Aktivitas</label>
              {{ Form::select('aktivitas[]', $list_aktivitas , $aktivitas, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
            </div>
            <div class="col-md-3">
              <label for="kualitas">Kualitas</label>
              {{ Form::select('kualitas[]', $list_kualitas , $kualitas, array('class' => 'form-control select2', 'multiple'=>'multiple')) }} 
            </div> 
            <button type="submit" class="btn btn-info col-md-1" style="margin-top: 23px;"><i class="fa fa-search"></i></button> 
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-aqua"><i class="fa fa-file-text-o"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Rencana Kerja VAT</span>
            <span class="info-box-number">{{ $total_rk }}</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-green"><i class="fa fa-truck"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Realisasi VAT</span>
            <span class="info-box-number">{{ $total_real }}</span>
          </div>
        </div>
      </div>


      <div class="clearfix visible-sm-block"></div>
      <div class="col-md-4 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-yellow"><i class="fa fa-bar-chart"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Rencana Kerja vs Realisasi</span>
            <span class="info-box-number">{{ $perc_rk_real }}<small>%</small></span>
          </div>
        </div>
      </div>

    </div>
  	<div class="row">
      <div class="col-md-6">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Rencana Kerja vs Realisasi</h3>
          </div>
          <div class="box-body">
            <canvas id="barChart1" style="height:230px; background-color: white; padding: 5px;"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Spraying Quality</h3>
          </div>
          <div class="box-body">
            <canvas id="barChart2" style="height:230px; background-color: white; padding: 5px;"></canvas>
            </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-7">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Poor Locations</h3>
          </div>
          <div class="box-body">
            <table class="table">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>PG</th>
                  <th>Kode Lokasi</th>
                  <th>Nama Aktivitas</th>
                  <th>Nama Unit</th>
                  <th>Kualitas</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach($list_data_rk_poor as $v)
                <tr>
                  <td>{{$v->tgl}}</td>
                  <td>{{$v->lokasi_grup}}</td>
                  <td>{{$v->lokasi_kode}}</td>
                  <td>{{$v->aktivitas_nama}}</td>
                  <td>{{$v->unit_label}}</td>
                  <td>{{$v->kualitas}}</td>
                  <td>
                    @if($v->kualitas=='Very Poor')
                      <i class="fa fa-square" style="color: #548235;"></i>
                    @else
                      <i class="fa fa-square" style="color: #72DB0F;"></i>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Boom Sprayer</h3>
          </div>
          <div class="box-body">
            <table class="table table-bordered">
              <tbody>
                <tr>
                  @foreach($list_data_unit_poor as $k=>$v)
                    <td class="text-center" style="font-weight: bold; font-size: 15pt;">{{ empty($v['Very Poor'])? '0' : $v['Very Poor'] }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 15pt;">{{ empty($v['Poor'])? '0' : $v['Poor'] }}</td>
                  @endforeach
                </tr>
                <tr>
                  @foreach($list_data_unit_poor as $k=>$v)
                    <td class="text-center">Very Poor</td>
                    <td class="text-center">Poor</td>
                  @endforeach
                </tr>
                <tr>
                  @foreach($list_data_unit_poor as $k=>$v)
                  <td colspan="2" class="text-center">{{$k}}</td>
                  @endforeach
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
@section("script")
<!-- ChartJS -->
{!! Html::script('AdminLTE-2.4.18/bower_components/chart.js/Chart.js') !!}
<script>
  $(function () {
    $("[data-widget='collapse']").click();
  $('#date_range').daterangepicker();
  var list_chart_1a = {!! $list_chart_1a !!}
  var list_chart_1b = {!! $list_chart_1b !!}
  var list_chart_2 = {!! $list_chart_2 !!}
  var barChartData1 = {
    labels  : list_chart_1a['label'],
    datasets: [
      {
        fillColor           : '#548235',
        strokeColor         : '#548235',
        pointColor          : '#548235',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : list_chart_1a['data'],
        label               : 'RK'
      },
      {
        fillColor           : '#72DB0F',
        strokeColor         : '#72DB0F',
        pointColor          : '#72DB0F',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : list_chart_1b['data'],
        label               : 'Report'
      }
    ]
  }
  var barChartData2 = {
    labels  : list_chart_2['label'],
    datasets: [
      {
        fillColor           : '#72DB0F',
        strokeColor         : '#72DB0F',
        pointColor          : '#72DB0F',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : list_chart_2['data']
      }
    ]
  }
  var barChartCanvas1  = $('#barChart1').get(0).getContext('2d')
  var barChartCanvas2  = $('#barChart2').get(0).getContext('2d')
  var barChart1        = new Chart(barChartCanvas1)
  var barChart2        = new Chart(barChartCanvas2)
  var barChartOptions                  = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero        : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - If there is a stroke on each bar
      barShowStroke           : true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth          : 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing         : 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing       : 1,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to make the chart responsive
      responsive              : true,
      maintainAspectRatio     : true
    }
  barChartOptions.datasetFill = false
  barChart1.Bar(barChartData1, barChartOptions)
  barChart2.Bar(barChartData2, barChartOptions)
  })
</script>
@stop
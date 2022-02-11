@extends('base_theme')
@section('style')
<style>
</style>
@stop
@section('content')
  <section class="content">
  	<div class="row">
      <div class="col-md-6">
      	<canvas id="barChart1" style="height:230px; background-color: white; padding: 5px;"></canvas>
      </div>
      <div class="col-md-6">
      	<canvas id="barChart2" style="height:230px; background-color: white; padding: 5px;"></canvas>
      </div>
    </div>
    <div class="row">
      <div class="col-md-7">
        <div class="box box-success">
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
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-5">
        <div class="box box-success">
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
{!! Html::script('AdminLTE-2.4.2/bower_components/chart.js/Chart.js') !!}
<script>
  $('#date_range').daterangepicker();
  var list_chart_1a = {!! $list_chart_1a !!}
  var list_chart_1b = {!! $list_chart_1b !!}
  var list_chart_2 = {!! $list_chart_2 !!}
  var barChartDataFC = {
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
  var barChartDataSC = {
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
  var barChartCanvasFC  = $('#barChart1').get(0).getContext('2d')
  var barChartCanvasSC  = $('#barChart2').get(0).getContext('2d')
  var barChartFC        = new Chart(barChartCanvasFC)
  var barChartSC        = new Chart(barChartCanvasSC)
  var barChartOptions                  = {
    title: {
        display: true,
        text: 'Custom Chart Title'
    },
    //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
    scaleBeginAtZero        : true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines      : true,
    //String - Colour of the grid lines
    scaleGridLineColor      : 'rgba(0,0,0,.05)',
    //Number - Width of the grid lines
    scaleGridLineWidth      : 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: false,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines  : false,
    //Boolean - If there is a stroke on each bar
    barShowStroke           : true,
    //Number - Pixel width of the bar stroke
    barStrokeWidth          : 2,
    //Number - Spacing between each of the X value sets
    barValueSpacing         : 5,
    //Number - Spacing between data sets within X values
    barDatasetSpacing       : 1,
    //String - A legend template
    // legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
    //Boolean - whether to make the chart responsive
    responsive              : true,
    maintainAspectRatio     : true
  }
  barChartOptions.datasetFill = false
  barChartFC.Bar(barChartDataFC, barChartOptions)
  barChartSC.Bar(barChartDataSC, barChartOptions)
</script>
@stop
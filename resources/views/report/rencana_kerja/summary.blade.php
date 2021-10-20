@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			Summary
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<div class="box-body">
						<table class="table">
						<tr>
							<th width="5%">Ritase</th>
							<th>Kecepatan Operasi</th>
							<th>Overlapping</th>
							<th>Ketepatan Dosis</th>
							<th>Waktu Spray per Ritase</th>
							<th>Golden Time</th>
							<th>Wing Level</th>
							<th>Hasil</th>
							<th>Kualitas</th>
						</tr>
						@foreach($list_rks as $k=>$v)
						<tr>
							@if($k==999999)
							<td colspan="7">Total Nilai Kualitas Spraying</td>
							@elseif($k==999)
							<td colspan="7">Area Not Spray</td>
							@else
							<td>{{$k}}</td>
							@endif
							
							@if($k<999)
								@foreach($v as $k2=>$v2)
								<td>{{$v2->nilai}}</td>
								@if($k2==6)
								<td>{{$v2->kualitas}}</td>
								@endif
								@endforeach
							@else
								@foreach($v as $k2=>$v2)
								<td>{{$v2->nilai}}</td>
								<td>{{$v2->kualitas}}</td>
								@endforeach
							@endif
						</tr>
						@endforeach
						</table>
					</div>
					<div class="box-footer">
						<a href="{{ url('/report/rencana_kerja') }}" class="btn btn-warning"> Back </a>
					</div>
				</div>
			</div>
		</div>
	</section>
@stop

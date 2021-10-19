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
							<th>Ritase</th>
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
							<td>{{$k==999 ? 'Area Not Spray' : $k}}</td>
							@if($k<999)
								@foreach($v as $k2=>$v2)
								<td>{{$v2->hasil}}</td>
								@if($k2==6)
								<td>{{$v2->kualitas}}</td>
								@endif
								@endforeach
							@else
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								@foreach($v as $k2=>$v2)
								<td>{{$v2->hasil}}</td>
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

@extends('base_theme')
@section("style")
    <style>
	    .table-responsive .table th, .table-responsive .table td {
	        white-space: nowrap !important;
	    }
    </style>
@stop
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
						<div class="box box-default box-solid">
							<div class="box-body">
								<table class="table">
									<tbody>
										<tr>
											<td width="15%">JENIS APLIKASI</td>
											<td>: {{$data->aktivitas_nama}}</td>
											<td width="10%">LOKASI</td>
											<td>: {{$data->lokasi_kode}}</td>
										</tr>
										<tr>
											<td>LUAS NETTO</td>
											<td>: {{$data->lokasi_lsnetto}} Ha</td>
											<td>LUAS BRUTO</td>
											<td>: {{$data->lokasi_lsbruto}} Ha</td>
										</tr>
										<tr>
											<td>JENIS APLIKASI</td>
											<td>: {{$data->aktivitas_nama}}</td>
											<td>LOKASI</td>
											<td>: {{$data->lokasi_kode}}</td>
										</tr>
										<tr>
											<td>JAM MULAI</td>
											<td>: {{$data->jam_mulai}}</td>
											<td>JAM SELESAI</td>
											<td>: {{$data->jam_selesai}}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="box box-default box-solid">
							<div class="box-body">
								<table class="table">
								<tr>
									<th width="5%">Ritase</th>
									@if(!empty($list_rks[1]))
										@foreach($list_rks[1] as $k2=>$v2)
										<th>{{$v2->parameter_nama}} <br/>{{ $v2->parameter_id<999 ? '('.$v2->standard.')' : '' }}</th>
										@endforeach
									@else
										<th colspan="6"></th>
										<th>Hasil</th>
									@endif
									<th>Kualitas</th>
								</tr>
								@foreach($list_rks as $k=>$v)
								<tr>
									@if($k==999999)
									<th colspan="7">Total Nilai Kualitas Spraying</th>
									@elseif($k==999)
									<th colspan="7">{{$v[0]->parameter_nama}} ({{$v[0]->standard}})</th>
									@else
									<td>{{$k}}</td>
									@endif
									
									@if($k<999)
										@foreach($v as $k2=>$v2)
										<td>{{number_format($v2->nilai,0)}} {{ $v2->parameter_id<999 ? '('.($v2->parameter_id!=5 ? number_format($v2->realisasi,2) : $v2->realisasi).')' : '' }}</td>
										@if($k2==6)
										<td>{{$v2->kualitas}}</td>
										@endif
										@endforeach
									@else
										@foreach($v as $k2=>$v2)
										<td>{{$v2->nilai}} {{ $v2->parameter_id<999 ? '('.$v2->realisasi.')' : '' }}</td>
										<td>{{$v2->kualitas}}</td>
										@endforeach
									@endif
								</tr>
								@endforeach
								</table>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<a href="{{ url('/report/rencana_kerja') }}" class="btn btn-warning"> Back </a>
					</div>
				</div>
			</div>
		</div>
	</section>
@stop

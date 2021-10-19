@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			Report Parameter Standard
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					{!! Form::open(['method' => 'PUT', 'route'=>['master.report_parameter_standard.detail_update', $data->id], 'class' => 'validated-form form-horizontal']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="grup_aktivitas_nama" class="col-md-4">Grup Aktivitas</label>
							<div class="col-md-6">
								{{ Form::text('grup_aktivitas_nama', $data->grup_aktivitas_nama, array('class' => 'form-control', 'readonly')) }}
							</div>
						</div>
						<div class="form-group">
							<label for="aktivitas_nama" class="col-md-4">Aktivitas</label>
							<div class="col-md-6">
								{{ Form::text('aktivitas_nama', $data->aktivitas_nama, array('class' => 'form-control', 'readonly')) }}
							</div>
						</div>
						<div class="form-group">
							<label for="nozzle_nama" class="col-md-4">Nozzle</label>
							<div class="col-md-6">
								{{ Form::text('nozzle_nama', $data->nozzle_nama, array('class' => 'form-control', 'readonly')) }}
							</div>
						</div>
						<div class="form-group">
							<label for="volume" class="col-md-4">Volume</label>
							<div class="col-md-6">
								{{ Form::text('volume', $data->volume, array('class' => 'form-control', 'readonly')) }}
							</div>
						</div>
						<hr/>
						<table class="table">
							<thead>
								<tr>
									<th width="30%">Parameter</th>
									<th>Dari</th>
									<th>Sampai</th>
									<th>Point</th>
									<th>Bobot</th>
									<th>Nilai</th>
								</tr>
							</thead>
							<tbody>
							@foreach($list_detail AS $v)
							<tr>
								<td>
									{{ Form::hidden('id[]', $v->id, array()) }}
									{{$v->report_parameter_nama.' - '.$v->status}}
								</td>
								<td>
									{{ Form::text("range_1[$v->id]", $v->range_1, array('class' => 'form-control')) }}
								</td>
								<td>
									{{ Form::text("range_2[$v->id]", $v->range_2, array('class' => 'form-control')) }}
								</td>
								<td>
									{{ Form::text("point[$v->id]", $v->point, array('class' => 'form-control')) }}
								</td>
								<td>
									{{ Form::text("bobot[$v->id]", $v->bobot, array('class' => 'form-control', 'readonly')) }}
								</td>
								<td>
									{{ Form::text("nilai[$v->id]", $v->nilai, array('class' => 'form-control', 'readonly')) }}
								</td>
							</tr>
							@endforeach
							</tbody>
						</table>
					</div>
					<div class="box-footer">
						{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/report_parameter_standard') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

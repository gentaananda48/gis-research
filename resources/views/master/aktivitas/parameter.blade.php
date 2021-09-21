@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Parameter Aktivitas
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the forms</h3>
					</div>
					{!! Form::open(['method' => 'PUT', 'route'=>['master.aktivitas.parameter_update', $data->id], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="kode">Kode</label>
							{{ Form::text('kode', $data->kode, array('placeholder' => 'Kode', 'class' => 'form-control', 'data-bv-message'=>'Required', 'readonly', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="nama">Nama</label>
							{{ Form::text('nama', $data->nama, array('placeholder' => 'Nama', 'class' => 'form-control', 'readonly')) }}
						</div>
						<table class="table">
							<thead>
								<tr>
									<th>Parameter</th>
									<th>Standard</th>
									<th>Minimal</th>
									<th>Maksimal</th>
									<th>Bobot</th>
								</tr>
							</thead>
						<tbody>
						@foreach($list_parameter AS $v)
						<tr>
							<td>
								{{ Form::hidden('id[]', $v->id, array()) }}
								{{$v->parameter_nama}}
							</td>
							<td>
								{{ Form::text("standard[$v->id]", $v->standard, array('class' => 'form-control')) }}
							</td>
							<td>
								{{ Form::text("minimal[$v->id]", $v->minimal, array('class' => 'form-control')) }}
							</td>
							<td>
								{{ Form::text("maximal[$v->id]", $v->maximal, array('class' => 'form-control')) }}
							</td>
							<td>
								{{ Form::text("bobot[$v->id]", $v->bobot, array('class' => 'form-control')) }}
							</td>
						</tr>
						@endforeach
						</tbody>
						</table>
					</div>
					<div class="box-footer">
						{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/aktivitas') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

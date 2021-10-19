@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Report Parameter Standard
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.report_parameter_standard'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="aktivitas_id">Aktivitas</label>
							{{ Form::select('aktivitas_id', $list_aktivitas, null, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="nozzle_id">Nozzle</label>
							{{ Form::select('nozzle_id', $list_nozzle, null, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="volume_id">Volume</label>
							{{ Form::select('volume_id', $list_volume, null, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
					</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/report_parameter_standard') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

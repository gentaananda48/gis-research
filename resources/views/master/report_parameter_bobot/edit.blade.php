@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Report Parameter Bobot
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'PUT', 'route'=>['master.report_parameter_bobot.update', $data->id], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="grup_aktivitas_id">Grup Aktivitas</label>
							{{ Form::select('grup_aktivitas_id', $list_grup_aktivitas, $data->grup_aktivitas_id, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="report_parameter_id">Report Parameter</label>
							{{ Form::select('report_parameter_id', $list_report_parameter, $data->report_parameter_id, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="bobot">Bobot</label>
							{{ Form::number('bobot', $data->bobot, array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
					</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/report_parameter_bobot') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

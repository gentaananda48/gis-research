@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Report Status
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.report_status'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="status">Status</label>
							{{ Form::text('status', Input::old('status'), array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="range_1">Range 1</label>
							{{ Form::text('range_1', Input::old('range_1'), array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="range_2">Range 2</label>
							{{ Form::text('range_2', Input::old('range_2'), array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="icon">Icon</label>
							{{ Form::text('icon', Input::old('icon'), array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
					</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/report_status') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

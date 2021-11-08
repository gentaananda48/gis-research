@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Volume Air
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.volume_air'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="volume">Volume</label>
							{{ Form::text('volume', Input::old('volume'), array('placeholder' => 'Volume', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required', 'autocomplete'=>'off')) }}
						</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/volume_air') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop
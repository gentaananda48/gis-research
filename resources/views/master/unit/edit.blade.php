@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Unit
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the forms</h3>
					</div>
					{!! Form::open(['method' => 'PUT', 'route'=>['master.unit.update', $data->id], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="label">Label</label>
							{{ Form::text('label', $data->label, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="box_id">BoxID</label>
							{{ Form::text('box_id', $data->box_id, array('class' => 'form-control', 'required', 'autocomplete'=>'off')) }}
						</div>
					</div>
					<div class="box-footer">
						<a href="{{ url('/master/bahan') }}" class="btn btn-warning"> Back </a>
						{{ Form::submit('Update', array('class' => 'btn btn-success pull-right'))}}
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

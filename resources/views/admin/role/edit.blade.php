@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Role
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the forms</h3>
					</div>
					{!! Form::open(['method' => 'PUT', 'route'=>['admin.role.update', $data->id], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="code">Code</label>
							{{ Form::text('code', $data->code, array('placeholder' => 'Code', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="name">Name</label>
							{{ Form::text('name', $data->name, array('placeholder' => 'Name', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
					</div>
					<div class="box-footer">
						{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/admin/role') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

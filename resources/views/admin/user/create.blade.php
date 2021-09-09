@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			User
		</h1>
	</section>

	<section class="content">
			<div class="row">
				<div class="col-md-6">
					<div class="box box-success">
						<div class="box-header with-border">
							<h3 class="box-title">Fill the forms</h3>
						</div>
						<div class="box-body">
							<div class="row">
								{!! Form::open(['method' => 'POST', 'route'=>['admin.user'], 'files' => true]) !!}
								<div class="col-md-12">
									<div class="form-group">
										<label for="username">Username</label>
										{{ Form::text('username', Input::old('username'), array('placeholder' => 'Username', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="name">Name</label>
										{{ Form::text('name', Input::old('name'), array('placeholder' => 'Name', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="email">Email</label>
										{{ Form::text('email', Input::old('email'), array('placeholder' => 'Email', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="phone">Phone</label>
										{{ Form::text('phone', Input::old('phone'), array('placeholder' => 'Phone', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="role_id">Role</label>
										{{ Form::select('role_id', $roles , Input::old('role_id'), array('class' => 'form-control select2')) }}
									</div>
									<div class="form-group">
										<label for="employee_id">Employee ID</label>
										{{ Form::text('employee_id', Input::old('employee_id'), array('placeholder' => 'Employee ID', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="password">Password</label>
										{{ Form::password('password', array('placeholder' => 'Password', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="password_confirmation">Password Confirmation</label>
										{{ Form::password('password_confirmation', array('placeholder' => 'Password Confirmation', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										{{ Form::file('image_file', ['class' => 'form-control']) }}
									</div>
								</div>
								<div class="col-md-12">
									<div class="col-md-2">
										{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
										{{ csrf_field() }}
									</div>
									<div class="col-md-2 col-md-offset-8">
										<a href="{{ url('/admin/user') }}" class="btn btn-warning"> Back </a>
									</div>
								</div>
								{{ Form::close() }}
							</div>
						</div>
					</div>
				</div>
			</div>
	</section>
@stop
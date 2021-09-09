@extends('base_theme')

@section('content')
	<div class="container-fluid">
		<section class="content-header">
			<h1>My Account</h1>
		</section>

		<section class="content">
			<div class="row">
				<div class="col-md-6">
					<div class="box box-success">
						<div class="box-header with-border">
							<h3 class="box-title">Fill the forms</h3>
						</div>
						{!! Form::open(['method' => 'PUT', 'route'=>['admin.user.update', $user->id], 'files' => true]) !!}
						<div class="box-body">
							<div class="row">
								{{ Form::hidden('page_name', 'myprofile') }}
								<div class="col-md-12">
									<div class="form-group">
										<img class="profile-user-img img-responsive img-circle" src="{{$user->avatar_thumb != "" ? $user->avatar_thumb : '/img/user.png'}}" alt="User profile picture">
										{{ Form::file('image_file', ['class' => 'form-control']) }}
									</div>
									<div class="form-group">
										<label for="username">Username</label>
										{{ Form::text('username', $user->username, array('placeholder' => 'Username', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="name">Name</label>
										{{ Form::text('name', $user->name, array('placeholder' => 'Name', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="email">Email</label>
										{{ Form::text('email', $user->email, array('placeholder' => 'Email', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="phone">Phone</label>
										{{ Form::text('phone', $user->phone, array('placeholder' => 'Phone', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="role_id">Role</label>
										{{ Form::hidden('role_id', $user->role_id) }}
										{{ Form::text('role_name', $user->role_name, array('placeholder' => 'Role', 'class' => 'form-control', 'readonly' => 'readonly')) }}
									</div>
									<div class="form-group">
										<label for="password">Password</label>
										{{ Form::password('password', array('placeholder' => 'Password', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="password_confirmation">Password Confirmation</label>
										{{ Form::password('password_confirmation', array('placeholder' => 'Password Confirmation', 'class' => 'form-control')) }}
									</div>
								</div>
							</div>
						</div>
						<div class="box-footer">
							{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
							<a href="{{ url('/') }}" class="btn btn-warning"> Back </a>
						</div>
						{{ Form::close() }}
					</div>
				</div>
			</div>
		</section>
	</div>
@stop
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
						{!! Form::open(['method' => 'PUT', 'route'=>['admin.user.update', $user->id], 'files' => true]) !!}
						{{ Form::hidden('page_name', 'admin') }}
						<div class="box-body">
							<div class="row">
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
										{{ Form::select('role_id', $roles , $user->role_id, array('class' => 'form-control select2')) }}
									</div>
									<div class="form-group">
										<label for="employee_id">Employee ID</label>
										{{ Form::text('employee_id', $user->employee_id, array('placeholder' => 'Employee ID', 'class' => 'form-control')) }}
									</div>
									<div class="form-group">
										<label for="area">PG</label>
										{{ Form::select('area', $list_area , $user->area, array('class' => 'form-control select2')) }}
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
										<label for="area">Status</label>
										{{ Form::select('status', $list_status , $user->status, array('class' => 'form-control select2')) }}
									</div>
								</div>
							</div>
						</div>
						<div class="box-footer">
							{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
							<a href="{{ url('/admin/user') }}" class="btn btn-warning"> Back </a>
						</div>
						{{ Form::close() }}
					</div>
				</div>
			</div>
	</section>

@stop

@section("script")
<script>
function ConfirmActivate() {
	    var x = confirm("Are you sure want to activate ?");
	    if (x)
	        return true;
	    else
	        return false;
	}
</script>
@stop
@extends('base_theme')

@section("style")
    <style>
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding: 0 4px;
        }
    </style>
@stop

@section('content')
	<section class="content-header">
		<h1>
			Role [{{ $role->name }}]
			<small>Permission</small>
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="box box-success">
			{!! Form::open(['method' => 'PUT', 'route'=>['admin.role.permission.update', $role->id]]) !!}
			<div class="box-body table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Name</th>
							<th>
								@if ($is_all_checked)
	                      			<input type="checkbox" class="checkbox-permission-all" checked>
	                  			@else
	                      			<input type="checkbox" class="checkbox-permission-all">
	                     		@endif
	                    	</th>
						</tr>
					</thead>
					<tbody>
					@foreach( $permissions as $k => $v)
						<tr bgcolor="#eee">
							<td colspan="2"><i class="fa {{ $v['icon'] }}" aria-hidden="true"><strong> {{ $k }}</strong></i></td>
						</tr>
						@foreach( $v['data'] as $k2 => $v2)
							<tr>
								<td style="text-indent: 10px;"><i class="fa fa-chevron-right" aria-hidden="true"> {{ $v2->name }}</i></td>
								<td width="7%">
									@if (in_array($v2->id,$role_permissions))
	                        			<input type="checkbox" class="checkbox-permission" name="permission[{{ $v2->id }}]" value="{{ $v2->id }}" checked>
	                            	@else
	                          			<input type="checkbox" class="checkbox-permission" name="permission[{{ $v2->id }}]" value="{{ $v2->id }}">
	                       			@endif
								</td>
							</tr>
						@endforeach
					@endforeach
					</tbody>
				</table>
			</div>
			<div class="box-footer">
				<div class="col-md-12">
					<div class="col-md-1">
						<a href="{{ url('/admin/role') }}" class="btn btn-warning"> Back </a>
					</div>
					<div class="col-md-1 col-md-offset-10">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						{{ csrf_field() }}
					</div>
				</div>
			</div>
			{{ Form::close() }}
		</div>
	</section>
@stop

@section('script')
<script>
$(function() {
    $('.checkbox-permission-all').on('click', function(){
        if($(this).is(':checked')){
            $('.checkbox-permission').prop('checked', true);
        } else {
            $('.checkbox-permission').prop('checked', false);
        }
    });
    $('.checkbox-permission').on('change', function(){
    	var is_all_checked = true;
	    $('.checkbox-permission').each(function(e){
			if($(this).is(':checked')){
	        } else {
				is_all_checked = false;
	        }
		});
		if(is_all_checked){
			$('.checkbox-permission-all').prop('checked', true);
		} else {
			$('.checkbox-permission-all').prop('checked', false);
		}
    });
});
</script>
@stop
@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Tindak Lanjut Pending
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the forms</h3>
					</div>
					{!! Form::open(['method' => 'PUT', 'route'=>['master.tindak_lanjut_pending.update', $data->id], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="nama">Nama</label>
							{{ Form::text('nama', $data->nama, array('placeholder' => 'Nama', 'class' => 'form-control')) }}
						</div>
					</div>
					<div class="box-footer">
						{{ Form::submit('Update', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/tindak_lanjut_pending') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop
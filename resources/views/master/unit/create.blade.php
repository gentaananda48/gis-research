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
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.unit'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="kode">Kode</label>
							{{ Form::text('kode', null, array('placeholder' => 'Kode', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="nama">Nama</label>
							{{ Form::text('nama', null, array('placeholder' => 'Nama', 'class' => 'form-control')) }}
						</div>
						<div class="form-group">
							<label for="lacak_id">ID (Lacak)</label>
							{{ Form::number('lacak_id', null, array('placeholder' => 'ID (Lacak)', 'class' => 'form-control')) }}
						</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/unit') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

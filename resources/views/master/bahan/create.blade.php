@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Bahan
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.bahan'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="kode">Kode</label>
							{{ Form::text('kode', Input::old('kode'), array('placeholder' => 'Kode', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="nama">Nama</label>
							{{ Form::text('nama', Input::old('nama'), array('placeholder' => 'Nama', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required', 'autocomplete'=>'off')) }}
						</div>
						<div class="form-group">
							<label for="kategori">Kategori</label>
	                		{{ Form::select('kategori[]', $list_kategori, null, array('class' => 'form-control select2', 'multiple'=>'multiple', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="uom">UOM</label>
							{{ Form::text('uom', Input::old('uom'), array('placeholder' => 'UOM', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/bahan') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

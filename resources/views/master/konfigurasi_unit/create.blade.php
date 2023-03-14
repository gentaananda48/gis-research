@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Konfigurasi Unit
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.konfigurasi_unit'], 'class' => 'validated-form']) !!}
					<div class="box-body">
						<div class="form-group">
							<label for="unit">Unit</label>
							{{ Form::select('unit', $list_unit, Input::old('unit'), array('class' => 'form-control select2', 'required')) }}
						</div>
						<div class="form-group">
							<label for="debit_kiri">Debit Liter Kiri</label>
							{{ Form::number('debit_kiri', Input::old('debit_kiri'), array('class' => 'form-control', 'required')) }}
						</div>
						<div class="form-group">
							<label for="debit_kanan">Debit Liter Kanan</label>
							{{ Form::number('debit_kanan', Input::old('debit_kanan'), array('class' => 'form-control', 'required')) }}
						</div>
						<div class="form-group">
							<label for="koefisien_sayap_kiri">Koefisien Saya Kiri</label>
							{{ Form::number('koefisien_sayap_kiri', Input::old('koefisien_sayap_kiri'), array('class' => 'form-control', 'step' => 'any', 'required')) }}
						</div>
						<div class="form-group">
							<label for="koefisien_sayap_kanan">Koefisien Saya Kanan</label>
							{{ Form::number('koefisien_sayap_kanan', Input::old('koefisien_sayap_kanan'), array('class' => 'form-control', 'step' => 'any', 'required')) }}
						</div>
						<div class="form-group">
							<label for="minimum_spray_kiri">Minimum Spray Kiri</label>
							{{ Form::number('minimum_spray_kiri', Input::old('minimum_spray_kiri'), array('class' => 'form-control', 'step' => 'any', 'required')) }}
						</div>
						<div class="form-group">
							<label for="minimum_spray_kanan">Minimum Spray Kanan</label>
							{{ Form::number('minimum_spray_kanan', Input::old('minimum_spray_kanan'), array('class' => 'form-control', 'step' => 'any', 'required')) }}
						</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/konfigurasi_unit') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

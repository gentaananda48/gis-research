@extends('base_theme')

@section('content')
	<section class="content-header">
		<h1>
			<i class="fa fa-edit"></i> 
			Lokasi
		</h1>
	</section>

	<section class="content container-fluid">
		<div class="row">
			<div class="col-md-6">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title">Fill the form</h3>
					</div>
					{!! Form::open(['method' => 'POST', 'route'=>['master.lokasi'], 'class' => 'validated-form']) !!}
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
							<label for="grup">PG</label>
	                		{{ Form::select('grup', $list_grup, null, array('class' => 'form-control select2', 'required' => 'required')) }}
						</div>
						<div class="form-group">
							<label for="bruto">Luas Bruto</label>
							{{ Form::text('lsbruto', Input::old('lsbruto'), array('placeholder' => 'Luas Bruto', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
						<div class="form-group">
							<label for="lsnetto">Luas Netto</label>
							{{ Form::text('lsnetto', Input::old('lsnetto'), array('placeholder' => 'Luas Netto', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
						<!-- <div class="form-group">
							<label for="status">Status</label>
							{{ Form::text('status', null, array('placeholder' => 'Status', 'class' => 'form-control')) }}
						</div> -->
						<div class="form-group">
							<label for="map_topleft">Map Top-Left</label>
							{{ Form::text('map_topleft', Input::old('map_topleft'), array('placeholder' => 'Map Top-left', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
						<div class="form-group">
							<label for="map_bottomright">Map Bottom-Right</label>
							{{ Form::text('map_bottomright', Input::old('map_bottomright'), array('placeholder' => 'Map Bottom-Right', 'class' => 'form-control', 'data-bv-message'=>'Required', 'required')) }}
						</div>
					<div class="box-footer">
						{{ Form::submit('Save', array('class' => 'btn btn-success'))}}
						<a href="{{ url('/master/lokasi') }}" class="btn btn-warning"> Back </a>
					</div>
					{{ csrf_field() }}
					{{ Form::close() }}
				</div>
			</div>
		</div>
	</section>
@stop

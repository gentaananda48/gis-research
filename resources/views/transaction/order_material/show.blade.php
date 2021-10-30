@extends('base_theme')

@section("style")
    <style>
        #contextMenu {
            position: absolute;
            display: none;
        }
        .nowrap {
            white-space: nowrap;
        }
	    .table-responsive .bootgrid-table th, .table-responsive .bootgrid-table td {
	        white-space: nowrap !important;
	    }
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding: 0 4px;
        }
    </style>
@stop

@section('content')
    <section class="content-header">
        <h1>
            <i class="fa fa-list"></i> 
            Order Material
        </h1>
    </section>

    <section class="content container-fluid">
    	<div class="row">
			<div class="col-md-6">
		        <div class="box box-success">
		            <div class="box-body">
		            	<div class="form-group">
							<label for="tanggal">Tanggal</label>
							{{ Form::text('tanggal', $data->tanggal, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="lokasi_nama">Lokasi</label>
							{{ Form::text('lokasi_nama', $data->lokasi_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="aktivitas_nama">Aktivitas</label>
							{{ Form::text('aktivitas_nama', $data->aktivitas_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="unit_label">Unit</label>
							{{ Form::text('unit_label', $data->unit_label, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="operator_nama">Operator</label>
							{{ Form::text('operator_nama', $data->operator_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="mixing_operator_nama">Mixing Operator</label>
							{{ Form::text('mixing_operator_nama', $data->mixing_operator_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="kasie_nama">Kasie</label>
							{{ Form::text('kasie_nama', $data->kasie_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
						<div class="form-group">
							<label for="status_nama">Status</label>
							{{ Form::text('status_nama', $data->status_nama, array('class' => 'form-control', 'readonly')) }}
						</div>
		            	<table class="table table-bordered">
		                	<thead>
		                		<tr>
		                			<th>BAHAN</th>
		                			<th style="text-align: right">QTY</th>
		                			<th>UOM</th>
		                		</tr>
		                	</thead>
		                	<hr/>
		                	<tbody>
		                		@foreach($data->bahan as $v)
		                		<tr>
		                			<td>{{ $v->bahan_nama  }}</td>
		                			<td align="right">{{ $v->qty  }}</td>
		                			<td>{{ $v->uom  }}</td>
		                		</tr>
		                		@endforeach
		                	</tbody>
		                </table>
		            </div>
		            <div class="box-footer">
						<a href="{{ url('/transaction/order_material') }}" class="btn btn-warning"> Back </a>
					</div>
		        </div>
		    </div>
		</div>
    </section>
@stop
@section("script")
@stop

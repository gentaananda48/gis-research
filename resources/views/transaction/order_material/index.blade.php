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
        <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target=".win-filter">Filter</button>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="commands" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
                            <th data-column-id="id" data-order="desc" data-type="numeric" data-identifier="true" data-visible="true">ID</th>
                            <th data-column-id="tgl" data-width="20%">Tanggal</th>
                            <th data-column-id="lokasi_kode">Lokasi</th>
                            <th data-column-id="aktivitas_nama">Aktivitas</th>
                            <th data-column-id="unit_label">Unit</th>
                            <th data-column-id="operator_nama">Operator</th>
                            <th data-column-id="mixing_operator_nama">Mixing Operator</th>
                            <th data-column-id="kasie_nama">Kasie</th>
                            <th data-column-id="om_status_nama">Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
<div class="modal fade win-filter" tabindex="-1" role="dialog" aria-labelledby="winFormMenuLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form class="form-horizontal">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Filter</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">   
                            <div class="form-group">
                                <label for="tgl" class="col-sm-4">Tanggal</label>
                                <div class="col-sm-8">
                                    {{ Form::text('tgl', null, array('id' => 'tgl', 'class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="shift" class="col-sm-4">Shift</label>
                                <div class="col-sm-8">
                                    {{ Form::select('shift[]', $list_shift , null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lokasi" class="col-sm-4">Lokasi</label>
                                <div class="col-sm-8">
                                    {{ Form::select('lokasi[]', $list_lokasi, null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>  
                            <div class="form-group">
                                <label for="aktivitas" class="col-sm-4">Aktivitas</label>
                                <div class="col-sm-8">
                                    {{ Form::select('aktivitas[]', $list_aktivitas, null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>    
                            <div class="form-group">
                                <label for="unit" class="col-sm-4">Unit</label>
                                <div class="col-sm-8">
                                    {{ Form::select('unit[]', $list_unit , null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>             
                        </div>
                        <div class="col-md-6">   
                            <div class="form-group">
                                <label for="nozzle" class="col-sm-4">Nozzle</label>
                                <div class="col-sm-8">
                                    {{ Form::select('nozzle[]', $list_nozzle , null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div> 
                            <div class="form-group">
                                <label for="volume" class="col-sm-4">Volume</label>
                                <div class="col-sm-8">
                                    {{ Form::select('volume[]', $list_volume , null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4">Status</label>
                                <div class="col-sm-8">
                                    {{ Form::select('status[]', $list_status , null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>        
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-save">OK</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section("script")
<script>
    $('#tgl').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });
    $('#tgl').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#tgl').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
</script>
{!! Html::script('/js/transaction/order_material.js') !!}
@stop

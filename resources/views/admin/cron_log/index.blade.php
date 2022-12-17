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
        .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
            padding: 0 4px;
        }
    </style>
@stop

@section('content')
<section class="content-header">
    <h1>
        Cron Log
    </h1>
</section>

<section class="content container-fluid">
    <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target=".win-filter">Filter <i class="fa fa-lg fa-search" aria-hidden="true"></i></button>
    <div class="box box-success">
        <div class="box-body table-responsive no-padding">
            <table id="grid-data" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th data-column-id="id" data-order="desc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                        <th data-column-id="name">Name</th>
                        <th data-column-id="status">Status</th>
                        <th data-column-id="remarks">Remarks</th>
                        <th data-column-id="created_at">CreatedAt</th>
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
                                <label for="name" class="col-sm-4">Name</label>
                                <div class="col-sm-8">
                                    {{ Form::select('name[]', $list_name , !empty($name)?$name:null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status" class="col-sm-4">Status</label>
                                <div class="col-sm-8">
                                    {{ Form::select('status[]', $list_status , !empty($status)?$status:null, array('class' => 'form-control select2', 'multiple'=>'multiple')) }}  
                                </div>
                            </div>      
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tgl" class="col-sm-4">Date</label>
                                <div class="col-sm-8">
                                    {{ Form::text('tgl', $tgl, array('id' => 'tgl', 'class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="remarks" class="col-sm-4">Remarks</label>
                                <div class="col-sm-8">
                                    {{ Form::text('remarks', !empty($remarks)?$remarks:null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
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
{!! Html::script('/js/admin/cron_log.js') !!}
@stop
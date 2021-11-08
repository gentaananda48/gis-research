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
        User
    </h1>
</section>

<section class="content container-fluid">
    <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target=".win-filter">Filter <i class="fa fa-lg fa-search" aria-hidden="true"></i></button>
    <a href="{{ url('/admin/user/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
    <div class="box box-success">
        <div class="box-body table-responsive no-padding">
            <table id="grid-data" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                        <th data-column-id="username" data-width="20%">Username</th>
                        <th data-column-id="name">Name</th>
                        <th data-column-id="email">Email</th>
                        <th data-column-id="phone">Phone</th>
                        <th data-column-id="role_name">Role</th>
                        <th data-column-id="employee_id" data-header-css-class="nowrap">Employee ID</th>
                        <th data-column-id="area">PG</th>
                        <th data-column-id="status" data-formatter="status">Status</th>
                        <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
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
                                <label for="username" class="col-sm-4">Username</label>
                                <div class="col-sm-8">
                                    {{ Form::text('username', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>  
                            <div class="form-group">
                                <label for="name" class="col-sm-4">Name</label>
                                <div class="col-sm-8">
                                    {{ Form::text('name', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>    
                            <div class="form-group">
                                <label for="email" class="col-sm-4">Email</label>
                                <div class="col-sm-8">
                                    {{ Form::text('email', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone" class="col-sm-4">Phone</label>
                                <div class="col-sm-8">
                                    {{ Form::text('phone', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>           
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role_name" class="col-sm-4">Role</label>
                                <div class="col-sm-8">
                                    {{ Form::text('role_name', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="employee_id" class="col-sm-4">Employee ID</label>
                                <div class="col-sm-8">
                                    {{ Form::text('employee_id', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>         
                            <div class="form-group">
                                <label for="status" class="col-sm-4">Status</label>
                                <div class="col-sm-8">
                                    {{ Form::text('status', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
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
{!! Html::script('/js/admin/user.js') !!}
@stop
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
            <i class="fa fa-list"></i> 
            Unit
        </h1>
    </section>

    <section class="content container-fluid">
    <a href="{{ url('/master/unit/sync') }}" class="btn btn-success btn-xs">Sync <i class="fa fa-download" aria-hidden="true"></i></a>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="true">ID</th>
                            <th data-column-id="label" data-width="20%">Label</th>
                            <th data-column-id="group_id">Group ID</th>
                            <th data-column-id="source_id" data-header-css-class="nowrap">Source ID</th>
                            <th data-column-id="source_device_id" data-header-css-class="nowrap">Source Device ID</th>
                            <th data-column-id="source_model" data-header-css-class="nowrap">Source Model</th>
                            <th data-column-id="source_phone" data-header-css-class="nowrap">Source Phone</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@stop

@section("script")
{!! Html::script('/js/master/unit.js') !!}
@stop

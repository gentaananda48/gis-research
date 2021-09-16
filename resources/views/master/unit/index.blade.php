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
    <a href="{{ url('/master/unit/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="kode" data-width="20%">Kode</th>
                            <th data-column-id="nama">Nama</th>
                            <th data-column-id="movement_status">Movement Status</th>
                            <th data-column-id="lacak_id">ID (Lacak)</th>
                            <th data-column-id="gps_updated">GPS Updated</th>
                            <th data-column-id="gps_signal_level">Signal Level</th>
                            <th data-column-id="gps_location_lat">Latitude</th>
                            <th data-column-id="gps_location_lng">Longitude</th>
                            <th data-column-id="gps_speed">Speed</th>
                            <th data-column-id="gps_heading">Heading</th>
                            <th data-column-id="gps_alt">Altitude</th>
                            <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
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

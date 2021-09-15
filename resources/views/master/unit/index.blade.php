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
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

<div id="contextMenu" class="dropdown clearfix">
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">
        <li>
            <a tabindex="-1" class="btn-edit" href="javascript:void(0)"><i class="fa fa-edit" aria-hidden="true"></i> Edit</a>
        </li>
        <li>
            <a tabindex="-1" class="btn-delete" href="javascript:void(0)"><i class="fa fa-trash" aria-hidden="true"></i> Delete</a>
        </li>
        <li class="divider"></li>
        <li>
            <a tabindex="-1" class="btn-refresh" href="javascript:void(0)"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</a>
        </li>
    </ul>
</div>
@stop

@section("script")
{!! Html::script('/js/master/unit.js') !!}
@stop

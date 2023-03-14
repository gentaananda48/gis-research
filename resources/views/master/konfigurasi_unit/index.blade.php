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
            Konfigurasi Unit
        </h1>
    </section>

    <section class="content container-fluid">
    <a href="{{ url('/master/konfigurasi_unit/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="unit" data-width="20%">Unit</th>
                            <th data-column-id="debit_kiri">DebitKiri</th>
                            <th data-column-id="debit_kanan">DebitKanan</th>
                            <th data-column-id="koefisien_sayap_kiri">KoefisienSayapKiri</th>
                            <th data-column-id="koefisien_sayap_kanan">KoefisienSayapKanan</th>
                            <th data-column-id="minimum_spray_kiri">MinimumSprayKiri</th>
                            <th data-column-id="minimum_spray_kanan">MinimumSprayKanan</th>
                            <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@stop

@section("script")
{!! Html::script('/js/master/konfigurasi_unit.js') !!}
@stop

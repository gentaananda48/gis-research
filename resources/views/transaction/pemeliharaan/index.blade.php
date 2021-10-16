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
            Pemeliharaan
        </h1>
    </section>

    <section class="content container-fluid">
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="tanggal" data-width="20%">Tanggal</th>
                            <th data-column-id="unit_label">Unit</th>
                            <th data-column-id="perbaikan">Perbaikan</th>
                            <th data-column-id="laporan_driver" data-header-css-class="nowrap" data-css-class="nowrap">Laporan Driver</th>
                            <th data-column-id="teknisi_nama">Teknisi</th>
                            <th data-column-id="tanggal_mulai" data-width="20%" data-header-css-class="nowrap" data-css-class="nowrap">Tanggal Mulai</th>
                            <th data-column-id="tanggal_selesai" data-width="20%" data-header-css-class="nowrap" data-css-class="nowrap">Tanggal Selesai</th>
                            <th data-column-id="catatan_teknisi" data-header-css-class="nowrap" data-css-class="nowrap">Catatan Teknisi</th>
                            <th data-column-id="lokasi_kode">Lokasi</th>
                            <th data-column-id="kasie_nama">Kasie</th>
                            <th data-column-id="status_nama">Status</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@stop

@section("script")
{!! Html::script('/js/transaction/pemeliharaan.js') !!}
@stop

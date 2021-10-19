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
            Koordinat Lokasi {{$lokasi->nama}}
        </h1>
    </section>
    <section class="content container-fluid">
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="bagian" data-width="20%">Bagian</th>
                            <th data-column-id="posnr">POSNR</th>
                            <th data-column-id="long">LONG</th>
                            <th data-column-id="latd">LATD</th>
                        </tr>
                    </thead>
                </table>
                <div class="box-footer text-right">
        		    <a class="btn btn-sm btn-success" href="/master/lokasi/">BACK</a>
        	    </div>
            </div>
        </div>
    </section>
@stop
@section("script")
<script>
    var LOKASI_ID = {{ $lokasi->id }}; 
</script>    
{!! Html::script('/js/master/koordinat_lokasi.js') !!}
@stop
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
            Bahan
        </h1>
    </section>

    <section class="content container-fluid">
    <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#import">
        Import  <i class="fa fa-sm fa-file-excel-o"></i>
    </button>
    <a href="{{ url('/master/bahan/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="kode" data-width="20%">Kode</th>
                            <th data-column-id="nama">Nama</th>
                            <th data-column-id="kategori_nama">Kategori</th>
                            <th data-column-id="uom">UOM</th>
                            <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

<div class="modal fade" id="import" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">IMPORT DATA</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('master.bahan.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>PILIH FILE</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section("script")
{!! Html::script('/js/master/bahan.js') !!}
@stop

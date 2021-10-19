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
            Lokasi
        </h1>
    </section>

    <section class="content container-fluid">
    @if ($message = Session::get('success'))
      <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert">×</button>    
          <strong>{{ $message }}</strong>
      </div>
    @endif

    @if ($message = Session::get('error'))
      <div class="alert alert-danger alert-block">
        <button type="button" class="close" data-dismiss="alert">×</button>    
        <strong>{{ $message }}</strong>
      </div>
    @endif    
<button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target=".win-filter">Filter</button>
    <a href="{{ url('/master/lokasi/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
    <button type="button" class="btn btn-xs btn-success" data-toggle="modal" data-target="#import">
        Import  <i class="fa fa-sm fa-file-excel-o"></i>
    </button>
    <!-- modal -->
    <div class="modal fade" id="import" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">IMPORT DATA</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('master.lokasi.import') }}" method="POST" enctype="multipart/form-data">
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
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="kode" data-width="20%">Kode</th>
                            <th data-column-id="nama">Nama</th>
                            <th data-column-id="grup">PG</th>
                            <th data-column-id="wilayah">Wilayah</th>
                            <th data-column-id="lsbruto" data-header-css-class="nowrap">Luas Bruto</th>
                            <th data-column-id="lsnetto" data-header-css-class="nowrap">Luas Netto</th>
                            <!-- <th data-column-id="status">Status</th> -->
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
                                <label for="kode" class="col-sm-4">Kode</label>
                                <div class="col-sm-8">
                                    {{ Form::text('kode', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>     
                            <div class="form-group">
                                <label for="nama" class="col-sm-4">Nama</label>
                                <div class="col-sm-8">
                                    {{ Form::text('nama', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>  
                        </div>
                        <div class="col-md-6"> 
                            <div class="form-group">
                                <label for="grup" class="col-sm-4">Grup</label>
                                <div class="col-sm-8">
                                    {{ Form::text('grup', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
                                </div>
                            </div>     
                            <div class="form-group">
                                <label for="wilayah" class="col-sm-4">Wilayah</label>
                                <div class="col-sm-8">
                                    {{ Form::text('wilayah', null, array('class' => 'form-control', 'autocomplete'=>'off')) }}
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
{!! Html::script('/js/master/lokasi.js') !!}
@stop

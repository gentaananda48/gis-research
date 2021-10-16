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
            Rencana Kerja
        </h1>
    </section>
    
    <section class="content container-fluid">
    @if ($message = Session::get('success'))
      <div class="alert alert-success alert-block">
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
                    <form action="{{ route('transaction.rencana_kerja.import') }}" method="POST" enctype="multipart/form-data">
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
                            <th data-column-id="tgl" data-width="20%">Tanggal</th>
                            <th data-column-id="shift_nama">Shift</th>
                            <th data-column-id="lokasi_kode">Lokasi</th>
                            <th data-column-id="lokasi_lsbruto">Luas Bruto</th>
                            <th data-column-id="lokasi_lsnetto">Luas Netto</th>
                            <th data-column-id="aktivitas_nama">Aktivitas</th>
                            <th data-column-id="unit_label">Unit</th>
                            <th data-column-id="operator_nama">Operator</th>
                            <th data-column-id="driver_nama">Driver</th>
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
{!! Html::script('/js/transaction/rencana_kerja.js') !!}
@stop

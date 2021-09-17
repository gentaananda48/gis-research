@extends('base_theme')

@section("style")
    <style>
        #contextMenu {
            position: absolute;
            display: none;
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
            Role
        </h1>
    </section>

    <section class="content container-fluid">
        <a href="{{ url('/admin/role/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
        <div class="box box-success">
            <div class="box-body table-responsive no-padding">
                <table id="grid-data" class="table table-hover table-bordered">
                    <thead>
                        <tr>
                            <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                            <th data-column-id="code" data-width="20%">Code</th>
                            <th data-column-id="name">Name</th>
                            <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
@stop

@section("script")
<!-- {!! Html::script('/js/admin/role.js') !!} -->
<script>
	$("#grid-data").bootgrid({
    css: {
        iconColumns: "fa fa-list",
        iconRefresh: "fa fa-refresh"
    },
    ajax: !0,
    ajaxSettings: {
        method: "GET",
        cache: !1
    },
    rowCount: [100],
    url: BASE_URL + "/admin/role/get" + window.location.search,
    formatters: {
        commands: function(a, t) {
            var e = '<a href="/admin/role/edit/' + t.id + '" class ="btn btn-xs btn-info">' + 'Edit' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>'  +
            ' <a href="/admin/role/permission/' + t.id + '" class ="btn btn-xs btn-primary">' + 'Permission' + ' <i class="fa fa-lock" aria-hidden="true"></i></a>';
            return e += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + 'Delete' + ' <i class="fa fa-trash"></i></a>';
        }
    }
    }).on("loaded.rs.jquery.bootgrid", function(a) {
        $(".btn-delete").on("click", function() {
            deleteData(BASE_URL + "/admin/role/delete/" + $(this).data("id"), this);
        });
    });
</script>
@stop

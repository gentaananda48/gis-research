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
        User
    </h1>
</section>

<section class="content container-fluid">
    <a href="{{ url('/admin/user/create') }}" class="btn btn-success btn-xs">Add <i class="fa fa-plus" aria-hidden="true"></i></a>
    <div class="box box-success">
        <div class="box-body table-responsive no-padding">
            <table id="grid-data" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th data-column-id="id" data-order="asc" data-type="numeric" data-identifier="true" data-visible="false">ID</th>
                        <th data-column-id="username" data-width="20%">Username</th>
                        <th data-column-id="name">Name</th>
                        <th data-column-id="email">Email</th>
                        <th data-column-id="phone">Phone</th>
                        <th data-column-id="role_name">Role</th>
                        <th data-column-id="employee_id" data-header-css-class="nowrap">Employee ID</th>
                        <th data-column-id="status" data-formatter="status">Status</th>
                        <th data-column-id="commands" data-width="15%" data-formatter="commands" data-align="center" data-header-align="center" data-sortable="false">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
@stop

@section("script")
<!-- {!! Html::script('/js/admin/user.js') !!} -->
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
    url: BASE_URL + "/admin/user/get" + window.location.search,
    formatters: {
        commands: function(a, t) {
            var e = '<a href="/admin/user/edit/' + t.id + '" class ="btn btn-xs btn-info">' + 'Edit' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>';
            return e += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + 'Delete' + ' <i class="fa fa-trash"></i></a>';
        }
    }
    }).on("loaded.rs.jquery.bootgrid", function(a) {
        $(".btn-delete").on("click", function() {
            deleteData(BASE_URL + "/admin/user/delete/" + $(this).data("id"), this);
        });
    });
</script>
@stop
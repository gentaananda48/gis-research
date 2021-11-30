var $contextMenu = $("#contextMenu");
$("#grid-data").bootgrid({
    css: {
        iconColumns: "fa fa-list",
        iconRefresh: "fa fa-refresh"
    },
    ajax: true,
    ajaxSettings: {
        method: "GET",
        cache: false
    },
    rowCount: [20],
    url: BASE_URL + "/admin/system_configuration/get" + window.location.search,
    formatters: {
        commands: function(a, t) {
            var e = '<a href="/admin/system_configuration/edit/' + t.id + '" class ="btn btn-xs btn-info">' + 'Edit' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>';
            return e += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + 'Delete' + ' <i class="fa fa-trash"></i></a>';
        }
    },
    navigation: 2,
}).on("loaded.rs.jquery.bootgrid", function(e) {
    $(".btn-delete").on("click", function() {
            deleteData(BASE_URL + "/admin/system_configuration/delete/" + $(this).data("id"), this);
        });
}).on("click.rs.jquery.bootgrid", function (e, columns, row){
    
});

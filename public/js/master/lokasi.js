$("#grid-data").bootgrid({
    css: {
        iconColumns: "fa fa-list",
        iconRefresh: "fa fa-refresh"
    },
    ajax: !0,
    ajaxSettings: {
        method: "GET",
        cache: false
    },
    rowCount: [100],
    url: BASE_URL + "/master/lokasi/get",
    formatters: {
        commands: function(a, t) {
            var e = '<a href="/master/lokasi/edit/' + t.id + '" class ="btn btn-xs btn-info">' + 'Edit' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>';
            return e += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + 'Delete' + ' <i class="fa fa-trash"></i></a>';
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {
    $(".btn-delete").on("click", function() {
        deleteData(BASE_URL + "/master/lokasi/delete/" + $(this).data("id"), this);
    });
});
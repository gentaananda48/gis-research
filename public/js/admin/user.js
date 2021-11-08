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
    url: BASE_URL + "/admin/user/get" + window.location.search,
    formatters: {
        commands: function(column, row) {
            var ret = '<a href="/admin/user/edit/' + row.id + '" class ="btn btn-xs btn-info">' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>';
            if(row.status=='inactive') {
                ret += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + row.id + '">' + ' <i class="fa fa-trash"></i></a>';
            }
            return ret;
        }
    },
    navigation: 2,
}).on("loaded.rs.jquery.bootgrid", function(e) {
    $(".btn-delete").on("click", function() {
        deleteData(BASE_URL + "/admin/user/delete/" + $(this).data("id"), this);
    });
});
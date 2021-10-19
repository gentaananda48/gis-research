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
    url: BASE_URL + "/master/lokasi/get" + window.location.search,
    navigation: 2,
    formatters: {
        commands: function(a, t) {
            var e = '<a href="/master/lokasi/edit/' + t.id + '" class ="btn btn-xs btn-info">' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>' +
                ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + ' <i class="fa fa-trash"></i></a>' +
                ' <a href="/master/lokasi/koordinat/' + t.id + '" class ="btn btn-xs btn-primary">' + ' Koordinat</a>'
            return e += ' <a href="/master/lokasi/map/' + t.id + '" class ="btn btn-xs btn-success">' + ' Maps</a>';
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {
    $(".btn-delete").on("click", function() {
        deleteData(BASE_URL + "/master/lokasi/delete/" + $(this).data("id"), this);
    });
});
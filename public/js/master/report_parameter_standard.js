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
    url: BASE_URL + "/master/report_parameter_standard/get" + window.location.search,
    navigation: 2,
    formatters: {
        commands: function(a, t) {
        	var e = '';
            e += '<a href="'+ BASE_URL + '/master/report_parameter_standard/edit/' + t.id + '" class ="btn btn-xs btn-info">' + ' <i class="fa fa-edit" aria-hidden="true"></i></a>';
            e += ' <a href="'+ BASE_URL + '/master/report_parameter_standard/detail/' + t.id + '" class ="btn btn-xs btn-info">' + ' <i class="fa fa-list" aria-hidden="true"></i></a>';
            e += ' <a class="btn btn-xs btn-danger btn-delete" role="button" data-id="' + t.id + '">' + ' <i class="fa fa-trash"></i></a>';
            return e;
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {
    $(".btn-delete").on("click", function() {
        deleteData(BASE_URL + "/master/report_parameter_standard/delete/" + $(this).data("id"), this);
    });
});
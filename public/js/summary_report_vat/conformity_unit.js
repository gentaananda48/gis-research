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
    url: BASE_URL + "/summary/conformity_unit/get" + window.location.search,
    navigation: 2,
    formatters: {}
}).on("loaded.rs.jquery.bootgrid", function(e) {
    //
}).on("click.rs.jquery.bootgrid", function (e, columns, row){
	//
});

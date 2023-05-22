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
    url: BASE_URL + "/admin/cron_log/get" + window.location.search,
    formatters: {
    },
    navigation: 2,
}).on("loaded.rs.jquery.bootgrid", function(e) {
	//
});
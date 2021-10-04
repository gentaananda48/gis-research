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
    url: BASE_URL + "/master/unit/get" + window.location.search,
    navigation: 2,
    formatters: {}
}).on("loaded.rs.jquery.bootgrid", function(a) {});
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
    url: BASE_URL + "/master/lokasi/koordinat/" + LOKASI_ID + "/get" + window.location.search,
    navigation: 2,
    formatters: {
        commands: function(a, t) {

        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {
    $(".btn-delete").on("click", function() {
        deleteData(BASE_URL + "/master/bahan/delete/" + $(this).data("id"), this);
    });
});
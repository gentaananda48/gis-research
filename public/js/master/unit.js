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
    formatters: {
        commands: function(column, row) {
            var ret = '<a href="/master/unit/track/' + row.id + '" class ="btn btn-xs btn-info">' + ' Tracking</a>';
            return ret += ' <a href="/master/unit/playback/' + row.id + '" class ="btn btn-xs btn-success">' + ' Playback</a>';
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {});
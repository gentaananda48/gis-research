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
            var ret = '<a href="'+ BASE_URL + '/master/unit/track/' + row.id + '" class ="btn btn-xs btn-info">' + ' Tracking</a>';
            // ret += ' <a href="'+ BASE_URL + '/master/unit/playback/' + row.id + '" class ="btn btn-xs btn-success">' + ' Playback (Cached)</a>';
            // return ret += ' <a href="'+ BASE_URL + '/master/unit/playback2/' + row.id + '" class ="btn btn-xs btn-success">' + ' Playback</a>';
            return ret += ' <a href="'+ BASE_URL + '/master/unit/playback/' + row.id + '" class ="btn btn-xs btn-success">' + ' Playback</a>';
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {});
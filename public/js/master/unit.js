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
            var ret = ' <a href="'+ BASE_URL + '/master/unit/edit/' + row.id + '" class ="btn btn-xs btn-default">' + ' <i class="fa fa-edit"></i></a>';
            ret += ' <a href="'+ BASE_URL + '/master/unit/track/' + row.id + '" class ="btn btn-xs btn-info">' + ' <i class="fa fa-map-marker"></i></a>';
            return ret += ' <a href="'+ BASE_URL + '/master/unit/playback/' + row.id + '" class ="btn btn-xs btn-success">' + ' <i class="fa fa-play"></i></a>';
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(a) {});
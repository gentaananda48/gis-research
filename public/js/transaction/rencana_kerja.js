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
    url: BASE_URL + "/transaction/rencana_kerja/get" + window.location.search,
    navigation: 2,
    formatters: {
        commands: function(column, row) {
            var ret = '';
            if(row.status_id==4) {
                ret += '<a href="'+ BASE_URL + '/report/rencana_kerja/playback/' + row.id + '" class ="btn btn-xs btn-success">' + ' Playback</a>';
            }
            return ret;
        }
    }
}).on("loaded.rs.jquery.bootgrid", function(e) {
    //
}).on("click.rs.jquery.bootgrid", function (e, columns, row){
	//
});

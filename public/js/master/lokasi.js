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
    url: BASE_URL + "/master/lokasi/get" + window.location.search,
    navigation: 2,
}).on("loaded.rs.jquery.bootgrid", function(e) {
    $(this).on("contextmenu", "tbody tr", function(e) {
        $('table tbody tr').removeClass('info');
        $(this).addClass('info');

        var id = $(this).attr('data-row-id');
        $contextMenu.css({
            display: "block",
            left: e.pageX,
            top: e.pageY
        });
        $contextMenu.find('.btn-edit').on('click', function() {
            location.href = BASE_URL + "/master/lokasi/edit/" + id;
        });
        $contextMenu.find('.btn-delete').on('click', function() {
            deleteData(BASE_URL + "/master/lokasi/delete/" + id, this);
        });
        $contextMenu.find('.btn-refresh').on('click', function() {
            location.reload();
        });
        return false;
    });
    $("html").on("click", function() {
        $contextMenu.hide();
    });
}).on("click.rs.jquery.bootgrid", function(e, columns, row) {
    $(this).find("tbody tr").removeClass('info');
    $(this).find("tbody tr[data-row-id=" + row.id + "]").addClass('info');
    $contextMenu.hide();
});
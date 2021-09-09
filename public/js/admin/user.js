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
    url: BASE_URL + "/admin/user/get" + window.location.search,
    formatters: {
        "status": function(column, row){
            return row.status_name + '<span class="hidden status-' + row.id + '">' + row.status  + '</span>';
        }
    },
    navigation: 2,
}).on("loaded.rs.jquery.bootgrid", function(e) {
    $(this).on("contextmenu", "tbody tr", function(e) {
        $('table tbody tr').removeClass('info');
        $(this).addClass('info');

        var id = $(this).attr('data-row-id');
        var status = $(this).find('.status-'+id).html();
        if(status == 'active'){
            $contextMenu.find('.btn-delete').show();
            $contextMenu.find('.btn-activate').hide();
            $contextMenu.find('.btn-delete').on('click', function(){
                deleteData(BASE_URL + "/admin/user/delete/" + id,this);
            });
        } else {
            $contextMenu.find('.btn-delete').hide();
            $contextMenu.find('.btn-activate').show();
            $contextMenu.find('.btn-activate').on('click', function(){
                var btn = this;
                btn.disabled=true;
                swal({
                    title: "Are you sure want to activate ?",
                    icon: "info",
                    buttons: [ "Cancel" , "OK"],
                    dangerMode: true,
                })
                .then(function(isConfirm) {
                    if (isConfirm) {
                        post(BASE_URL + "/admin/user/activate/" + id, [], 'PUT');
                    } else {
                        btn.disabled=false;
                        return false;
                    }
                });
            });
        }
        $contextMenu.css({
            display: "block",
            left: e.pageX,
            top: e.pageY
        });
        
        $contextMenu.find('.btn-edit').on('click', function(){
            location.href = BASE_URL + "/admin/user/edit/" + id;
        });
        
        $contextMenu.find('.btn-refresh').on('click', function(){
            location.reload();
        });
        return false;
    });
    $("html").on("click", function() {
         $contextMenu.hide();
    });
}).on("click.rs.jquery.bootgrid", function (e, columns, row){
    $(this).find("tbody tr").removeClass('info');
    $(this).find("tbody tr[data-row-id="+ row.id +"]").addClass('info');
    $contextMenu.hide();
});

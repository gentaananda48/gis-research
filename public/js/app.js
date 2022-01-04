$.QueryString = (function(a) {
    if (a == "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i){
        var p=a[i].split('=', 2);
        if (p.length != 2) continue;
        b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split('&'))

function get_new_query_string(current_page,record_id){
    var new_query = "";
    if (window.location.search.substr(1) != "") {
        window.location.search.substr(1).split("&").forEach(function(item,index){
            var query_item = item.split("=")[0];
            if (query_item != 'current_page' && query_item != 'record_id') {
                new_query += item + "&";
            }
        });
    }
    new_query += "current_page=" + current_page + "&record_id=" + record_id;
    return new_query;
}

function goTop(window) {
    var goTop = $('#goTop');

    (window.scrollTop() > 40) ? goTop.fadeIn(300) : goTop.fadeOut(300);
}

$(document).ready(function(){
    $(".select2").select2({ width: "null" });
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    //Date picker
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true
    });

    $(".multidate-picker").datepicker({ dateFormat: 'mm/dd/yy', multidate: true });
    $('.month-picker').datepicker({
        format: "mm-yyyy",
        startView: "months", 
        minViewMode: "months"
    });
    $(".year-picker").datepicker({
        format: "yyyy",
        viewMode: "years", 
        minViewMode: "years"
    });

    //Timepicker
    $('.timepicker').timepicker({
        showInputs: false
    })

    $('.validated-form').bootstrapValidator({
        excluded: [":disabled"],
        // feedbackIcons: {
        //     valid: 'glyphicon glyphicon-ok',
        //     invalid: 'glyphicon glyphicon-remove',
        //     validating: 'glyphicon glyphicon-refresh'
        // },
    });
    
    $('#goTop').on("click", function () {
        $('html, body').animate({'scrollTop': 0}, 1000);
    });

    $('.alert-custom').on('closed.bs.alert', function () {
        $('.section-alert').remove();
    })

    $(".section-alert").fadeTo(5000, 500).slideUp(500, function(){
        $(".section-alert").slideUp(500);
    });

    goTop($(window));

    $(window).scroll(function () {
        var me = $(this);
        goTop(me);
    });
});

/**
 * Number.prototype.format(n, x, s, c)
 * 
 * @param integer n: length of decimal
 * @param integer x: length of whole part
 * @param mixed   s: sections delimiter
 * @param mixed   c: decimal delimiter
 */
Number.prototype.format = function(n, x, s, c) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};

function post(path, params, method) {
    method = method || "post"; 

    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", path);

    if(method !== 'post'){
        var hiddenFieldMethod = document.createElement("input");
        hiddenFieldMethod.setAttribute("type", "hidden");
        hiddenFieldMethod.setAttribute("name", "_method");
        hiddenFieldMethod.setAttribute("value", method);

        form.appendChild(hiddenFieldMethod);
    }

    for ( var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }
    var hiddenFieldCSRF = document.createElement("input");
    hiddenFieldCSRF.setAttribute("type", "hidden");
    hiddenFieldCSRF.setAttribute("name", "_token");
    hiddenFieldCSRF.setAttribute("value", CSRF);

    form.appendChild(hiddenFieldCSRF);

    document.body.appendChild(form);
    form.submit();
}

function deleteData(path, btn){
    btn.disabled=true;
    swal({
        title: "Are you sure want to delete ?",
        icon: "warning",
        buttons: [ "Cancel" , "OK"],
        dangerMode: true,
    })
    .then(function(isConfirm) {
        if (isConfirm) {
            post(path, [], 'DELETE');
        } else {
            btn.disabled=false;
            return false;
        }
    });
};

function ConfirmDelete() {
    var x = confirm("Are you sure you want to delete ?");
    if (x)
        return true;
    else
        return false;
}

function confirmCancel(path) {
    swal({
        title: "Are you sure?",
        text: "Once cancelled, you will not be able to recover !",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then(function(isConfirm) {
        if (isConfirm) {
            post(path, [], 'delete');
        } else {
            return false;
        }
    });
}

function goToList(url){
    url == "" ? location.reload() : window.location.href = url;
}

function numberKeyUp(e){
    e.value = e.value.replace(/[^0-9\.\-]/g,'');
}

function numberFocus(e){
    e.value = e.value.replace(/[^0-9\.\-]/g,'');
}

function numberChange(e){
    e.value = numeral(e.value).format('0,0.00');
}

function numberBlur(e){
    e.value = numeral(e.value).format('0,0.00');
}

function revalidateField(e){
    $(e).closest('form').bootstrapValidator('revalidateField', $(e).prop('name'));
}



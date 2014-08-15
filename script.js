
function newseditsibmit(url) {
    document.getElementById('fksnewsadmineditvalue').value = url;
    document.getElementById('fks_news_admin_edit_form').return = "return true";
    document.getElementById('fks_news_admin_edit_form').submit();
}
;
function newspermsubmit() {
    document.getElementById('fksnewsadminperm').return = "return true";
    document.getElementById('fksnewsadminperm').submit();
}
;
function newsviewmoredef(ID) {
    document.getElementById(ID + "_div").style.display = 'none';
}
;
function newsviewmore(ID) {
    document.getElementById(ID + "_div").style.display = 'block';
    var IDfull = document.getElementById(ID + "_div");
    document.body.onmousemove = function(e) {
        var browserIE = document.all ? true : false;
        if (!browserIE) {
            document.captureEvents(Event.MOUSEMOVE);
        }
        ;
        IDfull.style.left = (browserIE ? event.clientX + document.body.scrollLeft : e.pageX) + "px";
        IDfull.style.top = (browserIE ? event.clientY + document.body.scrollTop : e.pageY) + "px";
    };
}
;
jQuery(function() {
    $('input.fksnewsinputperm').change(function() {
        //var $form=$(this);
        console.log($(this).attr("name"));
        console.log($(this).parent().parent().index());
        newsID = $(this).val(),
                $infodiv = $("#fks_news_admin_info" + $(this).parent().parent().index() + '_div');
        $infospan = $("#fks_news_admin_info" + $(this).parent().parent().index() + '_span');
        $.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {call: 'plugin_fksnewsfeed', name: 'local', id: newsID}, function(data) {
            alert('Received response' + data);
            console.log(data);
            $infodiv.html(
                    'author : ' + data['author'] + '<br>'
                    + 'email : ' + data['email'] + '<br>'
                    + 'date' + ': ' + data['newsdate']
                    + '<div class="fksnewsmoreinfotext">'
                    + data["text-html"]
                    + '</div>');
            $infospan.html(data["shortname"]);
            // data is array you returned with action.php
        },
                'json');


    });


    $('#load_new').submit(function(event) {
        event.preventDefault();
        var $form = $(this);
        newsID = $form.find("input[name='news_id_new']").val();
        $lostdiv = $('#lost_news');
        $.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {call: 'plugin_fksnewsfeed', name: 'local', id: newsID},
        function(data) {
            alert('Received response' + data);
            $lostdiv.html('<div class="fksnewsmoreinfotext">'
                    + data["fullhtml"]
                    + '</div>');
            // data is array you returned with action.php
        },
                'json');

    })
            ;

    function newsvalueup(ID) {
        if (ID !== maxfile) {
            ID++;
            newsvaluedown(ID);
        }
        ;
    }
    ;
    function newsvaluedown(ID) {
        var IDdown = ID - 1;
        var olddata = new Array();
        var el = document.getElementById('fks_news_admin_tr' + ID);

        if (ID !== 1) {
            olddata['fks_news_admin_id'] = document.getElementById('fks_news_admin_id' + ID).innerHTML;
            //olddata['fks_news_admin_edit'] = document.getElementById('fks_news_admin_edit' + ID).innerHTML;
            olddata['fks_news_admin_permold'] = document.getElementById('fks_news_admin_perm_old' + ID).innerHTML;
            olddata['fks_news_admin_view'] = document.getElementById('fks_news_admin_view' + ID).innerHTML;
            olddata['fks_news_admin_info'] = document.getElementById('fks_news_admin_info' + ID).innerHTML;
            olddata['fks_news_admin_permut_new_input'] = document.getElementById('fks_news_admin_permut_new_input' + ID).name;

            document.getElementById('fks_news_admin_id' + ID).innerHTML = document.getElementById('fks_news_admin_id' + IDdown).innerHTML;
            //document.getElementById('fks_news_admin_edit' + ID).innerHTML = document.getElementById('fks_news_admin_edit' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_perm_old' + ID).innerHTML = document.getElementById('fks_news_admin_perm_old' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_view' + ID).innerHTML = document.getElementById('fks_news_admin_view' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_info' + ID).innerHTML = document.getElementById('fks_news_admin_info' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_permut_new_input' + ID).name = document.getElementById('fks_news_admin_permut_new_input' + IDdown).name;
            document.getElementById('fks_news_admin_id' + IDdown).innerHTML = olddata['fks_news_admin_id'];
            //document.getElementById('fks_news_admin_edit' + IDdown).innerHTML = olddata['fks_news_admin_edit'];
            document.getElementById('fks_news_admin_perm_old' + IDdown).innerHTML = olddata['fks_news_admin_permold'];
            document.getElementById('fks_news_admin_view' + IDdown).innerHTML = olddata['fks_news_admin_view'];
            document.getElementById('fks_news_admin_info' + IDdown).innerHTML = olddata['fks_news_admin_info'];
            document.getElementById('fks_news_admin_permut_new_input' + IDdown).name = olddata['fks_news_admin_permut_new_input'];
        }
        ;
    }
    ;



    jQuery("h1.fkshover").click(function() {
        // var str=this.id;
        if (jQuery("div." + this.id).is(":hidden")) {
            jQuery("div." + this.id).slideDown();
        } else {
            jQuery("div." + this.id).slideUp();
        }
        ;
    });

    jQuery("td.fks_news_info").mouseover(function() {
        newsviewmore(this.id);
    });
    jQuery("td.fks_news_info").mouseout(function() {
        newsviewmoredef(this.id);
    });


    //jQuery("td > img").click(function() {
    $("img.fks_news_admin_down").click(function() {
        var value = jQuery(this).parent().parent().index();
        value++;
        /*for (var IDtr in value) {
         */ newsvaluedown(value)/*
          }
          ;*/
    });


    $("img.fks_news_admin_up").click(function() {
        var value = jQuery(this).parent().parent().index();
        value--;
        /*for (var IDtr in value) {
         */ newsvalueup(value)/*
          }
          ;*/
    });





    //});


    jQuery("#addtowiki").load(function() {
        setTimeout(function() {
//document.getElementById("addtowiki").onsubmit="return true";	
            document.getElementById("addtowiki").submit();
        }, 5000);
    });


});










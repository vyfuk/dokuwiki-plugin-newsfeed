if (toolbar) {
    toolbar[toolbar.length] = {"type": "fksnewsfeeds", "title": "Add news", "key": "",
        "icon": "../../plugins/fksnewsfeed/images/newsfeeds.png",
        "insert": "{{like>}}", "block": "false"
    };
}
;
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
        //IDfull.style.left = ((e || event).clientX + document.body.scrollLeft) + "px";
        //IDfull.style.top = ((e || event).clientY + document.body.scrollTop) + "px";
    };
}
;


function newsvalueup(ID) {
    if (ID !== maxfile) {
        ID++;
        newsvaluedown(ID);
    }
    ;
}
;




jQuery(function() {

    function newsvaluedown(ID) {
        var IDdown = ID - 1;
        var olddata = new Array();
        var el = document.getElementById('fks_news_admin_tr' + ID);

        if (ID !== 1) {
            olddata['fks_news_admin_id'] = document.getElementById('fks_news_admin_id' + ID).innerHTML;
            olddata['fks_news_admin_edit'] = document.getElementById('fks_news_admin_edit' + ID).innerHTML;
            olddata['fks_news_admin_permold'] = document.getElementById('fks_news_admin_perm_old' + ID).innerHTML;
            olddata['fks_news_admin_view'] = document.getElementById('fks_news_admin_view' + ID).innerHTML;
            olddata['fks_news_admin_info'] = document.getElementById('fks_news_admin_info' + ID).innerHTML;
            olddata['fks_news_admin_permut_new'] = document.getElementById('fks_news_admin_perm_new' + ID).name;
            document.getElementById('fks_news_admin_id' + ID).innerHTML = document.getElementById('fks_news_admin_id' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_edit' + ID).innerHTML = document.getElementById('fks_news_admin_edit' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_perm_old' + ID).innerHTML = document.getElementById('fks_news_admin_perm_old' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_view' + ID).innerHTML = document.getElementById('fks_news_admin_view' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_info' + ID).innerHTML = document.getElementById('fks_news_admin_info' + IDdown).innerHTML;
            document.getElementById('fks_news_admin_perm_new' + ID).name = document.getElementById('fks_news_admin_perm_new' + IDdown).name;
            document.getElementById('fks_news_admin_id' + IDdown).innerHTML = olddata['fks_news_admin_id'];
            document.getElementById('fks_news_admin_edit' + IDdown).innerHTML = olddata['fks_news_admin_edit'];
            document.getElementById('fks_news_admin_perm_old' + IDdown).innerHTML = olddata['fks_news_admin_permold'];
            document.getElementById('fks_news_admin_view' + IDdown).innerHTML = olddata['fks_news_admin_view'];
            document.getElementById('fks_news_admin_info' + IDdown).innerHTML = olddata['fks_news_admin_info'];
            document.getElementById('fks_news_admin_perm_new' + IDdown).name = olddata['fks_news_admin_permut_new'];
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
        value = maxfile - value - 1;
        /*for (var IDtr in value) {
         */ newsvaluedown(value)/*
          }
          ;*/
    });


    $("img.fks_news_admin_up").click(function() {
        var value = jQuery(this).parent().parent().index();
        value = maxfile - value - 1;
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










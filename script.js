
function viewnewsadmin(id) {
    document.getElementById(id).style.display = "block";
    var eeid = ['newsadd', 'newspermut'];
    for (var i in eeid) {
        if (eeid[i] !== id) {
            document.getElementById(eeid[i]).style.display = "none";
        }
        ;
    }
    ;
}
;
function viewsedit(id) {
    for (var i = 1; i < maxfile; i++) {
        if (id === i) {
            document.getElementById('newsedit' + id).style.display = (document.getElementById('newsedit' + id).style.display == "none") ? "block" : "none";
        } else {
            document.getElementById('newsedit' + i).style.display = "none";
        }
        ;
    }
    ;
}
;
window.onload = function newssubmit() {
    window.setTimeout(function() {
        //document.getElementById("addtowiki").onsubmit="return true";	
        document.getElementById("addtowiki").submit();
    }, 5000);
}
;

if (toolbar) {
    toolbar[toolbar.length] = {"type": "fksnewsfeeds", "title": "Add news", "key": "",
        "icon": "../../plugins/fksnewsfeed/images/newsfeeds.png",
        "insert": "{{like>}}", "block": "false"
    };
}
;
function newseditsibmit(url) {
    document.getElementById('fksnewsadmineditvalue').value = url;
    document.getElementById('fksnewsadminedit').return = "return true";
    document.getElementById('fksnewsadminedit').submit();
}
;
function newspermsubmit() {
    document.getElementById('fksnewsadminperm').return = "return true";
    document.getElementById('fksnewsadminperm').submit();
}
;
function newsviewmoredef(ID) {
    //setTimeout(function() {
    document.getElementById("fksnewsmoreinfo" + ID).style.display = 'none';
    //}, 200);
    //document.getElementById("fksnewsmoreinfo"+ID).style.display='none' ;
}
function newsviewmore(ID) {
    document.getElementById("fksnewsmoreinfo" + ID).style.display = 'block';
    var IDfull = document.getElementById("fksnewsmoreinfo" + ID);

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

function newsvaluedown(ID) {
    var IDdown = ID - 1;
    var olddata = new Array();
    if (ID !== 1) {
        olddata['fksnewsadminid'] = document.getElementById('fksnewsadminid' + ID).innerHTML;
        olddata['fksnewsadminedit'] = document.getElementById('fksnewsadminedit' + ID).innerHTML;
        olddata['fksnewsadminpermold'] = document.getElementById('fksnewsadminpermold' + ID).innerHTML;
        olddata['fksnewsadminview'] = document.getElementById('fksnewsadminview' + ID).innerHTML;
        olddata['fksnewsadmininfo'] = document.getElementById('fksnewsadmininfo' + ID).innerHTML;
        olddata['fkspermutnewname'] = document.getElementById('fkspermutnew' + ID).name;

        document.getElementById('fksnewsadminid' + ID).innerHTML = document.getElementById('fksnewsadminid' + IDdown).innerHTML;
        document.getElementById('fksnewsadminedit' + ID).innerHTML = document.getElementById('fksnewsadminedit' + IDdown).innerHTML;
        document.getElementById('fksnewsadminpermold' + ID).innerHTML = document.getElementById('fksnewsadminpermold' + IDdown).innerHTML;
        document.getElementById('fksnewsadminview' + ID).innerHTML = document.getElementById('fksnewsadminview' + IDdown).innerHTML;
        document.getElementById('fksnewsadmininfo' + ID).innerHTML = document.getElementById('fksnewsadmininfo' + IDdown).innerHTML;
        document.getElementById('fkspermutnew' + ID).name = document.getElementById('fkspermutnew' + IDdown).name;

        document.getElementById('fksnewsadminid' + IDdown).innerHTML = olddata['fksnewsadminid'];
        document.getElementById('fksnewsadminedit' + IDdown).innerHTML = olddata['fksnewsadminedit'];
        document.getElementById('fksnewsadminpermold' + IDdown).innerHTML = olddata['fksnewsadminpermold'];
        document.getElementById('fksnewsadminview' + IDdown).innerHTML = olddata['fksnewsadminview'];
        document.getElementById('fksnewsadmininfo' + IDdown).innerHTML = olddata['fksnewsadmininfo'];
        document.getElementById('fkspermutnew' + IDdown).name = olddata['fkspermutnewname'];
    };
}
;





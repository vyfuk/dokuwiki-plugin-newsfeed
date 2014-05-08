
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
    for (i = 1; i < maxfile; i++) {
        if (id == i) {
            document.getElementById('newsedit' + id).style.display = (document.getElementById('newsedit' + id).style.display == "none") ? "block" : "none";
        } else {
            document.getElementById('newsedit' + i).style.display = "none";
        }
        ;
    }
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
    setTimeout(function() {
        document.getElementById("fksnewsmoreinfo" + ID).style.display = 'none';
    }, 200);
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
    var valueid = document.getElementById("fkspermutnew" + ID).value;
    var IDup = ID;
    IDup++;
    if (document.getElementById("fkspermutnew" + IDup).value !== null) {

        var valueupid = document.getElementById("fkspermutnew" + IDup).value;
        document.getElementById("fkspermutnew" + IDup).value = valueid;
        document.getElementById("fkspermutnew" + ID).value = valueupid;
    }
    ;
}
;

function newsvaluedown(ID) {
    var valueid = document.getElementById("fkspermutnew" + ID).value;
    var IDdown = ID;
    IDdown--;
    if (document.getElementById("fkspermutnew" + IDdown).value !== null) {

        var valuedownid = document.getElementById("fkspermutnew" + IDdown).value;
        document.getElementById("fkspermutnew" + IDdown).value = valueid;
        document.getElementById("fkspermutnew" + ID).value = valuedownid;
    }
    ;

}
;




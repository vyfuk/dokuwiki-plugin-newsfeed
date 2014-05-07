
function viewnewsadmin(id) {
    document.getElementById(id).style.display = "block";
    var eeid = ['newsadd', 'newsedit', 'newsdelete', 'newspermut'];
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



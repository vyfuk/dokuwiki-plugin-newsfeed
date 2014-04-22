
function viewnewsadmin(id) {
    if (id === "newsedit") {
        if (document.getElementById(id).style.display == "none") {
            document.getElementById(id).style.display = "block";
            document.getElementById("newsadd").style.display = "none";
            document.getElementById("newsdelete").style.display = "none";
        }

    }
    else {
        if (id === "newsadd") {
            if (document.getElementById(id).style.display == "none") {
                document.getElementById(id).style.display = "block";
                document.getElementById("newsedit").style.display = "none";
                document.getElementById("newsdelete").style.display = "none";
            }
        } else {
            if (document.getElementById(id).style.display == "none") {
                document.getElementById(id).style.display = "block";
                document.getElementById("newsedit").style.display = "none";
                document.getElementById("newsadd").style.display = "none";

            }
        }
    }
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



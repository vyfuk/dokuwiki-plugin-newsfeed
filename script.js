
function viewnewsadmin(id) {
    document.getElementById(id).style.display = "block";
    var eeid = ['newsadd', 'newsedit', 'newsdelete', 'newspermut'];
    for (var i in eeid) {
        if (eeid[i] !== id) {
            document.getElementById(eeid[i]).style.display = "none";
        };
    };
    //switch (id) {
    //   case "newsadd":
//
    //          break;
    //    case "newsedit":
//
    //          break;
    //    case "newsdelete":
//
    //          break;
    //    case "newspermut":
//
    //          break;
    //}
    //if (id === "newsedit") {
    //  if (document.getElementById(id).style.display == "none") {
    //     document.getElementById(id).style.display = "block";
    //      document.getElementById("newsadd").style.display = "none";
    //   document.getElementById("newsdelete").style.display = "none";
    //}

    //}
    //else {
    //  if (id === "newsadd") {
    //    if (document.getElementById(id).style.display == "none") {
    //      document.getElementById(id).style.display = "block";
    //    document.getElementById("newsedit").style.display = "none";
    //  document.getElementById("newsdelete").style.display = "none";
    //}
//        } else {
    //           if (document.getElementById(id).style.display == "none") {
    //             document.getElementById(id).style.display = "block";
    //           document.getElementById("newsedit").style.display = "none";
    //         document.getElementById("newsadd").style.display = "none";

    //          }
//        }
    //}
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



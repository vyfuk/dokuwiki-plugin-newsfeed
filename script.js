/*
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
 
 */

jQuery(function() {


    /*$('input.fksnewsinputperm').change(function() {
     //var $form=$(this);
     
     console.log($(this).attr("name"));
     console.log($(this).parent().parent().parent().index());
     newsID = $(this).val(),
     $infodiv = $("#fks_news_admin_info" + $(this).parent().parent().parent().index() + '_div');
     $infospan = $("#fks_news_admin_info" + $(this).parent().parent().parent().index() + '_span');
     $.post(
     DOKU_BASE + 'lib/exe/ajax.php',
     {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', id: newsID}, function(data) {
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
     
     
     });*/


    /* $('#load_new').submit(function(event) {
     event.preventDefault();
     var $form = $(this);
     newsID = $form.find("input[name='news_id_lost']").val();
     newsdir = $form.find("input[name='news_dir_lost']").val();
     $lostdiv = $('#lost_news');
     $.post(
     DOKU_BASE + 'lib/exe/ajax.php',
     {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', id: newsID, dir: newsdir},
     function(data) {
     console.log(data);
     //alert('Received response' + data);
     $lostdiv.html('<div class="fksnewsmoreinfotext">'
     + data["fullhtml"]
     + '</div>');
     // data is array you returned with action.php
     },
     'json');
     
     })
     ;
     
     
     */

    /**
     * edit button for news on page
     */


    /*   function newsvalueup(ID) {
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
     
     if (ID !== 0) {
     olddata['fks_news_admin_permut_new_input'] = document.getElementById('fks_news_admin_permut_new_input' + ID).value;
     document.getElementById('fks_news_admin_permut_new_input' + ID).value = document.getElementById('fks_news_admin_permut_new_input' + IDdown).value;
     document.getElementById('fks_news_admin_permut_new_input' + IDdown).value = olddata['fks_news_admin_permut_new_input'];
     }
     ;
     }
     ;
     
     
     
     
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
     newsvaluedown(value)
     }
     ;
     });
     
     
     $("img.fks_news_admin_up").click(function() {
     var value = jQuery(this).parent().parent().index();
     value--;
     /*for (var IDtr in value) {
     newsvalueup(value)
     }
     ;
     });
     
     
     
     
     
     //});
     
     
     jQuery("#addtowiki").load(function() {
     setTimeout(function() {
     //document.getElementById("addtowiki").onsubmit="return true";	
     document.getElementById("addtowiki").submit();
     }, 5000);
     });
     
     */
});

jQuery(function() {
    _edit_news();
    _more_news();

    jQuery(window).load(function() {
        $('div.fks_news_stream').each(function() {
            event.preventDefault();
            $stream = $(this);
            $(this).append('<img src="http://img.ffffound.com/static-data/assets/6/77443320c6509d6b500e288695ee953502ecbd6d_m.gif">');
            var newsSTREAM = $(this).data("stream");
            var newsFEED = $(this).data("feed");

            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', do: 'stream', stream: newsSTREAM, feed: newsFEED},
            function(data) {
                $stream.html(data["r"]);
                _edit_news();
                _more_news();

            },
                    'json');

        });


        ;

    })
            ;
    function _edit_news() {
        $('div.fksnewseven,div.fksnewsodd').mouseover(function() {

            event.preventDefault();
            var newsID = $(this).data("id");

            $editdiv = $('div.fks_edit[data-id=' + $(this).data("id") + ']');
            if ($editdiv.html() !== "") {
                return false;
            }
            ;

            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', do: 'edit', id: newsID},
            function(data) {
                $editdiv.html(data["r"]);
            }, 'json');

        });
    }
    ;
    function _more_news() {
        $('div.fks_news_more').click(function() {
            event.preventDefault();
            
            var newsVIEW = $(this).data("view");
            var newsSTREAM = $(this).data("stream");
            $streamdiv = $('div.fks_news_stream[data-stream=' + newsSTREAM + ']');
            $(this).append('<img src="http://img.ffffound.com/static-data/assets/6/77443320c6509d6b500e288695ee953502ecbd6d_m.gif">');
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', do: 'more', stream: newsSTREAM, view: newsVIEW},
            $.proxy(function(data) {
                $(this).html("");
                $streamdiv.html($streamdiv.html() + data["r"]);
                _edit_news();
                _more_news();
            }, this)
            , 'json');

        });
    }
    ;
});











jQuery(function() {

    var $ = jQuery;
    _edit_news();
    _more_news();
    _link_news();

    jQuery(window).load(function() {
        $('div.FKS_newsfeed_stream').each(function() {
            //event.preventDefault();
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
                _link_news();

            },
                    'json');

        });


        ;

    })
            ;

    function _edit_news() {
        $('div.FKS_newsfeed_even,div.FKS_newsfeed_odd').mouseover(function() {

            // event.preventDefault();
            var newsID = $(this).data("id");

            $editdiv = $('div.FKS_newsfeed_edit[data-id=' + $(this).data("id") + ']');
            if ($editdiv.html() !== "") {
                return false;
            }
            ;

            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', do: 'edit', id: newsID},
            function(data) {
                $editdiv.html(data["r"]);
                _link_news();
            }, 'json');
            

        });
    }
    ;
    function _more_news() {
        $('div.FKS_newsfeed_more').click(function() {
            //event.preventDefault();

            var newsVIEW = $(this).data("view");
            var newsSTREAM = $(this).data("stream");
            $streamdiv = $('div.FKS_newsfeed_stream[data-stream=' + newsSTREAM + ']');
            $(this).append('<img src="http://img.ffffound.com/static-data/assets/6/77443320c6509d6b500e288695ee953502ecbd6d_m.gif">');
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', do: 'more', stream: newsSTREAM, view: newsVIEW},
            $.proxy(function(data) {
                $(this).html("");
                $streamdiv.html($streamdiv.html() + data["r"]);
                _edit_news();
                _more_news();
                _link_news();
            }, this)
                    , 'json');

        });
    }
    ;
    
    function _link_news() {
        $('button.FKS_newsfeed_link_btn').click(function() {
            var ID=$(this).data('id');
            $('input.FKS_newsfeed_link_inp[data-id='+ID+']').slideToggle();
        });
    }
    ;




});










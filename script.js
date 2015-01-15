


jQuery(function() {
    var $ = jQuery;
    _edit_news();
    _more_news();

    
    function _edit_news() {
        $('div.fksnewseven,div.fksnewsodd').mouseover(function() {

           // event.preventDefault();
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
            //event.preventDefault();
            
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
    jQuery(window).load(function() {
        $('div.fks_news_stream').each(function() {
            
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
});










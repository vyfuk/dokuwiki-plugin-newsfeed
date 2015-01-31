
jQuery(function() {

    var $ = jQuery;
    _edit_news();
    _more_news();
    _link_news();
    jQuery(window).load(function() {
        $('div.FKS_newsfeed_stream').each(function() {

            var $stream = $(this);
            $(this).append(_add_load_bar());
            _start_load_animation();
            
            var newsSTREAM = $(this).data("stream");
            var newsFEED = $(this).data("feed");
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'stream', news_stream: newsSTREAM, news_feed: newsFEED},
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
            var $editdiv = $('div.FKS_newsfeed_edit[data-id=' + $(this).data("id") + ']');
            if ($editdiv.html() !== "") {
                return false;
            }
            ;
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'edit', news_id: newsID},
            function(data) {
                $editdiv.html(data["r"]);
                _link_news();
                _news_share_FB();
            }, 'json');
        });
    }
    ;
    function _more_news() {
        $('div.FKS_newsfeed_more').click(function() {
            //event.preventDefault();

            var newsVIEW = $(this).data("view");
            var newsSTREAM = $(this).data("stream");
            var $streamdiv = $('div.FKS_newsfeed_stream[data-stream=' + newsSTREAM + ']');
            $(this).append(_add_load_bar());
            _start_load_animation();
           
            //$(this).append('<img src="http://img.ffffound.com/static-data/assets/6/77443320c6509d6b500e288695ee953502ecbd6d_m.gif">');
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'more', news_stream: newsSTREAM, news_view: newsVIEW},
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
            var ID = $(this).data('id');
            $('input.FKS_newsfeed_link_inp[data-id=' + ID + ']').slideDown();
        }
        );
    }
    ;
    function _news_share_FB() {
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id))
                return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_PI/sdk.js#xfbml=1&version=v2.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }
    ;
    function _add_load_bar() {

        return '<div class="progress">' +
                '<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 30%">' +
                '<span class="sr-only"></span>' +
                '</div>' +
                '</div>';
    }
    function _start_load_animation() {
        var $load = $('.progress-bar');
        console.log($load.css("width"));
        $load.animate({
            width: 99+"%"

        }, 4000,"linear");
        ;
    }
    ;
    return true;
});










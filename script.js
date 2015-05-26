/**
 * JavaScript for doku plugin FKSnewsfeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */

jQuery(function () {

    var $ = jQuery;
    _edit_news();
    _more_news();
    _link_news();
    $(window).load(function () {

        $('div.FKS_newsfeed_stream').each(function () {
            var $stream = $(this);
            $(this).append(_add_load_bar());
            _start_load_animation();

            var newsSTREAM = $(this).data("stream");
            var newsFEED = $(this).data("feed");
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'stream', news_stream: newsSTREAM, news_feed: newsFEED},
            function (data) {
                $stream.html(data["r"]);
                _edit_news();
                _more_news();
                _link_news();
                _link_rss();
                _news_manage();
            },
                    'json');
        });
        ;
    })
            ;
    function _edit_news() {
        $('div.FKS_newsfeed_even,div.FKS_newsfeed_odd').mouseover(function () {
            var newsID = $(this).data("id");
            var $editdiv = $('div.FKS_newsfeed_edit[data-id=' + $(this).data("id") + ']');
            if ($editdiv.html() !== "") {
                return false;
            }
            ;
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'edit', news_id: newsID},
            function (data) {
                $editdiv.html(data["r"]);
                _link_news();
                _news_share_FB();
            }, 'json');
        });
    }
    ;
    function _more_news() {
        $('div.FKS_newsfeed_more').click(function () {
            //event.preventDefault();

            var newsVIEW = $(this).data("view");
            var newsSTREAM = $(this).data("stream");
            var $streamdiv = $('div.FKS_newsfeed_stream[data-stream=' + newsSTREAM + ']');
            $(this).append(_add_load_bar());
            _start_load_animation();
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'more', news_stream: newsSTREAM, news_view: newsVIEW},
            $.proxy(function (data) {
                $(this).html("");
                $streamdiv.html($streamdiv.html() + data["news"]);
                if (data['more']) {
                    $('div.FKS_newsfeed_more[data-stream=' + newsSTREAM + ']').remove();
                }
                _edit_news();
                _more_news();
                _link_news();
                _link_rss();
                _news_manage();
            }, this)
                    , 'json');
        });
    }

    function _add_news() {
        var $addForm = $('div.FKS_newsfeed_order_add');
        console.log($addForm.find('input.button'));

        $addForm.find('input.button').click(function () {
            var newsWEIGTH = $addForm.find('input[name=weight]').val();
            var newsID = $addForm.find('input[name=news_id]').val();
            var newsSTREAM = $addForm.find('input[name=news_stream]').val();
            // console.log(newsID+"<-ID"+newsSTREAM+'<-stream'+newsWEIGTH);
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed',
                        target: 'feed',
                        name: 'local',
                        news_do: 'add',
                        news_id: newsID,
                        news_weight: newsWEIGTH,
                        news_stream: newsSTREAM},
            function (data) {
                console.log(data);
                $('.FKS_newsfeed_delete_stream').append(data["order_div"]);
                _edit_news();
                _more_news();
                _link_news();
                _link_rss();
                _news_manage();
                sortNewsDivs();
            }, 'json');
        });
    }
    ;
    function _link_news() {
        $('button.FKS_newsfeed_link_btn').click(function () {
            var ID = $(this).data('id');
            $('input.FKS_newsfeed_link_inp[data-id=' + ID + ']').slideDown();
        }
        );
    }
    ;
    function _news_manage() {
        $('.FSK_newsfeed_manage_btn').click(function () {
            $('.FKS_newsfeed_manage').slideDown();
        });
    }
    ;
    function _link_rss() {
        $('button.FKS_newsfeed_rss_btn').click(function () {

            $('input.FKS_newsfeed_rss_inp').slideDown();
        }
        );
    }
    ;
    function _news_share_FB() {
        (function (d, s, id) {
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

        $load.animate({
            width: 99 + "%"

        }, 4000, "linear");
        ;
    }

    $(window).load(function () {
        sortNewsDivs();
        _add_news();
    });


    function sortNewsDivs() {
        var $input = $('.FKS_newsfeed_delete_stream_news_weight').find('input');

        var weights = new Array();
        $('.FKS_newsfeed_delete_stream_news').each(function () {
            var id = $(this).data("id");
            var weight = $(this).find('input.edit').val();
            var index = $(this).data("index");
            //console.log(weight);
            //console.log(id);
            weights[index] = {id: id, weight: Number(weight), index: index};
        });
        weights.sort(sortByWeight);
        //console.log(weights);
        var pos = 0;
        for (var k in weights) {
            var news = weights[k];
            $('.FKS_newsfeed_delete_stream_news[data-index=' + news.index + ']').each(function () {
                $(this).animate({top: pos}, "slow");
                var height = $(this).height();
                pos += height;
            });
        }
        $('.FKS_newsfeed_delete_stream').css({height: pos});

        $input.change(function () {
            sortNewsDivs();
        });
    }

    function sortByWeight(a, b) {
        return((a.weight < b.weight) ? 1 : ((a.weight > b.weight) ? -1 : 0));
    }

    return true;
});










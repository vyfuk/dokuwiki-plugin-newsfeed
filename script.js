/**
 * JavaScript for DokuWiki plugin FKSnewsfeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB */
jQuery(function () {
    var $ = jQuery;
    var FKS_newsfeed = {
        div_more_news: 'div.more_news',
        div_feed: 'div.even,div.odd',
        div_stream: 'div.stream'
    };
    var $FKS_newsfeed = $('div.FKS_newsfeed');
    $('span[contenteditable="true"]').live("click", function () {
        document.execCommand('selectAll', false, null);
    });
    $(window).load(function () {
        _FB_newsfeed();

        $FKS_newsfeed.find(FKS_newsfeed.div_stream).each(function () {
            var $stream = $(this);
            $(this).append(AddLoadBar());
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {
                        call: 'plugin_fksnewsfeed',
                        target: 'feed',
                        name: 'local',
                        news_do: 'stream',
                        news_stream: $(this).data("stream"),
                        news_feed_s: 0,
                        news_feed_l: $(this).data("feed")
                    },
            function (data) {
                $stream.html(data["r"]);
                FB.XFBML.parse();

            },
                    'json');
        });
    });

    $FKS_newsfeed.find(FKS_newsfeed.div_more_news).find('button.button').live("click", function () {

        var $div_more_news = $(this).parent(FKS_newsfeed.div_more_news);
        var $streamdiv = $(this).parents(FKS_newsfeed.div_stream);
        $div_more_news.html("");
        $div_more_news.append(AddLoadBar());
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_fksnewsfeed',
                    target: 'feed',
                    name: 'local',
                    news_do: 'more',
                    news_stream: $div_more_news.data("stream"),
                    news_view: $div_more_news.data("view"),
                    news_feed_s: $div_more_news.data("view"),
                    news_feed_l: 3
                },
        function (data) {
            $div_more_news.html("");
            $streamdiv.append(data["r"]);
            if (data['more']) {
                $FKS_newsfeed.find(FKS_newsfeed.div_more_news).remove();
            }
            FB.XFBML.parse();

        }
        , 'json');
    });

    $FKS_newsfeed.find('.opt_btn').live("click", function () {
        $(this).toggleClass('active');
        $(this).parents('.edit').find('.opt').slideToggle();
    });

    $FKS_newsfeed.find('.priority_btn').live("click", function () {
        $(this).toggleClass('active');
        $(this).parents('.edit').find('.priority').slideToggle();

    });
    $FKS_newsfeed.find('.share_btn').live("click", function () {
        $(this).toggleClass('active');
        $(this).parents('.edit').find('.share').slideToggle();

    });

    /*
     * @TODO 
     */

    function AddLoadBar() {
        return '<div class="load" style="text-align:center;clear:both">' +
                '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
                '</div>';
    }
    /**
     * button to delete newsfeed on manage
     */
    $FKS_newsfeed.find('#warning').live("click", function (event) {

        if (confirm(LANG.plugins.fksnewsfeed.oRlyDelete)) {
            return true;
        } else {

            return false;

        }
    });
    return true;
});

function _FB_newsfeed() {
    (function (d, s, id) {
        console.log(d);
        console.log(s);
        console.log(id);
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id))
            return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
}
;

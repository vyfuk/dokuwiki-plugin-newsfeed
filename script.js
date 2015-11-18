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
        try {
            _FB_newsfeed();
        }
        catch (e) {
        }
        try {
            _Tweet_newsfeed();
        } catch (e) {
        }

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
                twttr.widgets.load();
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
            twttr.widgets.load();
            FB.XFBML.parse();

        }
        , 'json');
    });

    $('form#FKS_stream_choose').find('select').live("change", function () {
        console.log(this);
       // $(this).parents('form').submit();
    });


    $FKS_newsfeed.find('.btns button').live("click", function () {
        var fcls = $(this).attr('class');
        var cls = '';
        if (fcls.match(/.*opt.*/)) {
            cls = 'opt';
        } else if (fcls.match(/.*priority.*/)) {
            cls = 'priority';
        } else if (fcls.match(/.*share.*/)) {
            cls = 'share';
        } else {
            return;
        }
        var a = false;
        if (fcls.match(/.*active.*/)) {
            a = true;
        }
        $(this).parents('.btns').find('button').each(function () {
            $(this).removeClass('active');
        });
        $(this).parents('.edit').find('.fields .field').slideUp();
        if (!a) {
            $(this).toggleClass('active');
            $(this).parents('.edit').find('.' + cls).slideToggle();
        }
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

        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id))
            return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
}

function _Tweet_newsfeed() {
    !function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
        if (!d.getElementById(id)) {
            js = d.createElement(s);
            js.id = id;
            js.src = p + '://platform.twitter.com/widgets.js';
            fjs.parentNode.insertBefore(js, fjs);
        }
    }(document, 'script', 'twitter-wjs');

}
window.twttr = (_Tweet_newsfeed());

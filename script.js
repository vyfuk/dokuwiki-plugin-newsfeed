/**
 * JavaScript for DokuWiki plugin FKSnewsfeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE */
jQuery(function () {
    var $ = jQuery;
    var FKS_newsfeed = {
        div_simple_order: 'div.simple_order_div',
        div_delete_news: 'div.delete_news',
        div_order_stream: 'div.order_stream',
        div_add_to_stream: 'div.add_to_stream',
        div_more_news: 'div.more_news',
        div_feed: 'div.even,div.odd',
        div_stream: 'div.stream'
    };
    var $FKS_newsfeed = $('div.FKS_newsfeed');
    $('span[contenteditable="true"]').live("click", function () {
        document.execCommand('selectAll', false, null);
    });
    $(window).load(function () {
        //sortNewsDivs();
        $FKS_newsfeed.find(FKS_newsfeed.div_stream).each(function () {
            var $stream = $(this);
            $(this).append(_add_load_bar());
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
            },
                    'json');
        });
    });
    $FKS_newsfeed.find('.priority_btn').live("click", function () {
        $(this).parent().find('.priority').slideToggle();

    });
    $FKS_newsfeed.find(FKS_newsfeed.div_more_news).find('button.button').live("click", function () {

        var $div_more_news = $(this).parent(FKS_newsfeed.div_more_news);
        var $streamdiv = $(this).parents(FKS_newsfeed.div_stream);
        $div_more_news.html("");
        $div_more_news.append(_add_load_bar());
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
        }
        , 'json');
    });

    $FKS_newsfeed.find('button.link_btn').live("click", function (event) {
        $(this).toggleClass('active');
        $(this).parent('div').children('span').slideToggle();
    });
    /*
     * @TODO 
     */
    $('button.FKS_newsfeed_rss_btn').live("click", function () {
        $('input.FKS_newsfeed_rss_inp').slideDown();
    });
    function _add_load_bar() {
        return '<div class="load" style="text-align:center;clear:both">' +
                '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
                '</div>';
    }
    /**
     * button to delete newsfeed on manage
     */
    $FKS_newsfeed.find('#warning').live("click", function () {
        if (confirm(LANG.plugins.fksnewsfeed.oRlyDelete)) {
        }
    });
    return true;
});
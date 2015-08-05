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
    $(window).load(function () {
        sortNewsDivs();
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
                        news_feed: $(this).data("feed")
                    },
            function (data) {
                $stream.html(data["r"]);
            },
                    'json');
        });
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
                    news_view: $div_more_news.data("view")
                },
        function (data) {
            $div_more_news.html("");
            $streamdiv.append(data["news"]);
            if (data['more']) {
                $FKS_newsfeed.find(FKS_newsfeed.div_more_news).remove();
            }
        }
        , 'json');
    });
    /**
     * 
     * button to add news to strem
     */
    var $add_to_stream = $FKS_newsfeed.find(FKS_newsfeed.div_add_to_stream);
    $add_to_stream.find('input.button').click(function () {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_fksnewsfeed',
                    target: 'feed',
                    name: 'local',
                    news_do: 'add',
                    news_id: $add_to_stream.find('input[name=news_id]').val(),
                    news_weight: $add_to_stream.find('input[name=weight]').val(),
                    news_stream: $add_to_stream.find('input[name=news_stream]').val()
                },
        function (data) {
            $FKS_newsfeed.find(FKS_newsfeed.div_order_stream).append(data["order_div"]);
            sortNewsDivs();
        }, 'json');
    });
    $FKS_newsfeed.find('button.link_btn').live("click", function (event) {
        $(this).parent('div').children('input').slideToggle();
    });
    /*
     * @TODO 
     */
    $('button.FKS_newsfeed_rss_btn').live("click", function () {
        $('input.FKS_newsfeed_rss_inp').slideDown();
    });
    function _add_load_bar() {
        return '<div class="load" style="text-align:center;clear:both">' +
                '<img src="'+DOKU_BASE+'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
                '</div>';
    }
    /**
     * button to delete newsfeed on manage
     */
    $FKS_newsfeed.find(FKS_newsfeed.div_delete_news).find('button').live("click",function () {
        if (confirm(LANG.plugins.fksnewsfeed.oRlyDelete)) {
            $(this).parent(FKS_newsfeed.div_delete_weight).children('input.edit').val(0);
            $(this).parents(FKS_newsfeed.div_simple_order).slideUp();
            sortNewsDivs();
        }
    });
    function sortNewsDivs() {
        var $input = $FKS_newsfeed.find(FKS_newsfeed.div_delete_news).find('input');
        var weights = new Array();
        $FKS_newsfeed.find(FKS_newsfeed.div_simple_order).each(function () {
            var index = $(this).data("index");
            weights[index] = {id: $(this).data("id"), weight: Number($(this).find('input.edit').val()), index: index};
        });
        weights.sort(sortByWeight); 
        var pos = 0;
        for (var k in weights) {
            var news = weights[k];
            
            $FKS_newsfeed.find(FKS_newsfeed.div_simple_order + '[data-index=' + news.index + ']').each(function () {
                
                $(this).animate({top: pos}, "slow");
                var height = $(this).height();
                pos += height;
                pos += 50;
            });
        }
        $FKS_newsfeed.find(FKS_newsfeed.div_order_stream).css({height: pos});
        $input.one("change",function () {
            sortNewsDivs();            
        });
    }
    function sortByWeight(a, b) {
        return((a.weight < b.weight) ? 1 : ((a.weight > b.weight) ? -1 : 0));
    }
    return true;
});










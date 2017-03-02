/**
 * JavaScript for DokuWiki plugin FKS-NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB, JSINFO */
jQuery(function () {
    var $ = jQuery;
    const CALL_PLUGIN = 'plugin_fksnewsfeed';
    const CALL_TARGET = 'feed';
    const CALL_MORE = 'more';
    const CALL_STREAM = 'stream';

    const init = function (stream, feed, renderInit) {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news_do: CALL_STREAM,
                news_stream: stream,
                news_feed_s: 0,
                news_feed_l: feed,
                page_id: JSINFO.id

            },
            renderInit,
            'json');
    };

    const loadNext = function (stream, view, start, length, renderNext) {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news_do: CALL_MORE,
                news_stream: stream,
                news_view: view,
                news_feed_s: start,
                news_feed_l: length,
                page_id: JSINFO.id
            },
            renderNext
            , 'json');
    };

    const loadBar = function () {
        return '<div class="load-bar" style="text-align:center;clear:both">' +
            '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
            '</div>';
    };


    $('div.FKS_newsfeed').each(function () {
        "use strict";
        var $container = $(this);
        var $streamContainer = $container.find('.stream').eq(0).append(loadBar());

        const renderNews = function (data) {
            data.html.news.forEach(function (news) {
                $streamContainer.append(news);
            });
            $streamContainer.append(data.html.button);
        };

        const removeLoadBar = function () {
            $streamContainer.find('.load-bar').remove();
        };

        const renderInit = function (data) {
            removeLoadBar();
            $container.prepend(data.html.head);
            renderNews(data);
        };

        const renderNext = function (data) {
            removeLoadBar();
            renderNews(data);
        };

        init($streamContainer.data('stream'), $streamContainer.data('feed'), renderInit);

        $container.on('click', '.more_news', function () {
            var $buttonContainer = $(this);
            var start = $buttonContainer.data('view');
            var stream = $buttonContainer.data('stream');
            $streamContainer.append(loadBar());
            $buttonContainer.remove();
            loadNext(stream, start, start, 3, renderNext);
        });

        $container.on('click', '.more_option_toggle', function () {
            $(this).siblings('.fields').slideToggle();
        });
    });

    try {
        _Tweet_newsfeed();
    } catch (e) {
    }
    return true;
});


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

/**
 * JavaScript for DokuWiki plugin FKS-NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB, JSINFO */
jQuery(function () {
    var $ = jQuery;
    const CALL_PLUGIN = 'plugin_news-feed';
    const CALL_TARGET = 'feed';
    const CALL_MORE = 'more';
    const CALL_STREAM = 'stream';

    const init = function (stream, start, feed, renderInit) {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news: {
                    do: CALL_STREAM,
                    stream: stream,
                    length: feed,
                    start: start,
                },
                page_id: JSINFO.id

            },
            renderInit,
            'json');
    };

    const loadNext = function (stream, start, length, renderNext) {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news: {
                    do: CALL_MORE,
                    stream: stream,
                    start: start,
                    length: length
                },
                page_id: JSINFO.id
            },
            renderNext
            , 'json');
    };

    const loadBar = function () {
        return '<div class="load-bar col-lg-12" style="text-align:center;clear:both">' +
            '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
            '</div>';
    };


    $('div.news-stream').each(function () {
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
            console.log(data);
            removeLoadBar();
            renderNews(data);
        };
        var start = +$streamContainer.data('start');
        init($streamContainer.data('stream'), start ? start : 0, $streamContainer.data('feed'), renderInit);

        $container.on('click', '.more-news', function () {
            const $buttonContainer = $(this);
            var start = $buttonContainer.data('view');
            var stream = $buttonContainer.data('stream');
            $streamContainer.append(loadBar());
            $buttonContainer.remove();
            loadNext(stream, start, 3, renderNext);
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

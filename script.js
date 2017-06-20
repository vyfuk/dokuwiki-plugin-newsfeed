/**
 * JavaScript for DokuWiki plugin FKS-NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB, JSINFO */
jQuery(function () {
    "use strict";
    let $ = jQuery;
    const CALL_PLUGIN = 'plugin_news-feed';
    const CALL_TARGET = 'feed';
    const CALL_MORE = 'more';
    const CALL_STREAM = 'stream';

    const fetch = (stream, start, length, action, callback) => {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news: {
                    do: action,
                    stream,
                    start,
                    length
                },
                page_id: JSINFO.id
            },
            callback,
            'json');
    };

    const loadBar = '<div class="load-bar w-100" style="text-align:center;clear:both">' +
        '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
        '</div>';


    $('div.news-stream').each(function () {
        const $container = $(this);
        const $streamContainer = $container.find('.stream').eq(0).append(loadBar);

        const renderNews = (data) => {
            data.html.news.forEach(function (news) {
                $streamContainer.append(news);
            });
            if (window.PluginSocial) {
                window.PluginSocial.parse();
            }
        };

        const removeLoadBar = () => {
            $streamContainer.find('.load-bar').remove();
        };

        const renderNext = (data) => {
            removeLoadBar();
            renderNews(data);
            $streamContainer.append(data.html.button);
        };

        const renderInit = (data) => {
            $container.prepend(data.html.head);
            renderNext(data);
        };

        const start = +$streamContainer.data('start');
        fetch($streamContainer.data('stream'), start ? start : 0, $streamContainer.data('feed'), CALL_STREAM, renderInit);

        $container.on('click', '.more-news', function () {
            const $buttonContainer = $(this);
            const start = $buttonContainer.data('view');
            const stream = $buttonContainer.data('stream');
            $streamContainer.append(loadBar);
            $buttonContainer.remove();
            fetch(stream, start, 3, CALL_MORE, renderNext);
        });
    });

    return true;
});

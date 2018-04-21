/**
 * JavaScript for DokuWiki plugin FKS-NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global DOKU_BASE, JSINFO, PluginSocial, jQuery */
jQuery(() => {
    'use strict';
    const CALL_PLUGIN = 'plugin_news-feed';
    const CALL_TARGET = 'feed';

    const fetch = (stream, start, length, callback) => {
        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news: {
                    stream,
                    start,
                    length,
                },
                page_id: JSINFO.id,
            },
            callback,
            'json');
    };

    document.querySelectorAll('.news-stream').forEach((stream) => {
        let index = 0;
        const newsContainer = stream.querySelector('.stream');
        const streamName = newsContainer.getAttribute('data-stream');
        const numberFeed = newsContainer.getAttribute('data-feed');
        const loadBar = stream.querySelector('.load-bar');
        const moreButton = stream.querySelector('.more-news');

        const renderNews = (data) => {
            index += data.html.news.length;
            if (!data.html.news.length) {
                moreButton.disabled = true;
            }
            data.html.news.forEach((news) => {
                newsContainer.innerHTML += news;
            });
            if (window.PluginSocial) {
                PluginSocial.parse();
            }
        };
        const renderNext = (data) => {
            loadBar.style.display = 'none';
            moreButton.disabled = false;
            renderNews(data);
        };

        fetch(streamName, index, numberFeed, renderNext);
        moreButton.addEventListener('click', () => {
            loadBar.style.display = '';
            moreButton.disabled = true;
            fetch(streamName, index, 3, renderNext);
        });
    });
    return true;
});

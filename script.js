/**
 * JavaScript for DokuWiki plugin NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global DOKU_BASE, JSINFO */
window.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const fetchNews = (stream, offset, length, callback) => {
        fetch(DOKU_BASE + 'lib/exe/ajax.php?call=plugin_newsfeed', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                call: 'plugin_newsfeed',
                target: 'feed',
                news: {
                    stream,
                    offset,
                    length,
                },
                page_id: JSINFO.id,
            })
        }).then((response) => {
            return response.json();
        }).then((data) => {
            callback(data);
        });
    };

    document.querySelectorAll('.news-stream').forEach((stream) => {
        let index = 0;
        const newsContainer = stream.querySelector('.stream');
        const streamName = newsContainer.getAttribute('data-stream');
        const numberFeed = newsContainer.getAttribute('data-feed');
        const loadBar = stream.querySelector('.load-bar');
        const moreButton = stream.querySelector('.more-news');

        const renderNext = (data) => {
            loadBar.style.display = 'none';
            moreButton.disabled = false;
            index += data.html.news.length;
            if (!data.html.news.length) {
                moreButton.disabled = true;
            }
            data.html.news.forEach((news) => {
                newsContainer.innerHTML += news;
            });
        };
        fetchNews(streamName, index, numberFeed, renderNext);
        moreButton.addEventListener('click', () => {
            loadBar.style.display = '';
            moreButton.disabled = true;
            fetchNews(streamName, index, 3, renderNext);
        });
    });
    return true;
});

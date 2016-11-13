/**
 * JavaScript for DokuWiki plugin FKSnewsfeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB, JSINFO,*/
"use strict";


jQuery(function () {
    var $ = jQuery;
    var $streamContainers = $('.stream-container');


    try {
        _Tweet_newsfeed();
    } catch (e) {
    }
    $streamContainers.each(function () {
        var start = 0;


        var $streamContainer = $(this);
        const stream = $streamContainer.data('stream');
        var $feedContainer = $(this).find('.feed-container');
        $feedContainer.addLoad = function () {
            $(this).append('<div class="load-gif">' +
                '<img src="' + DOKU_BASE + 'lib/plugins/fksnewsfeed/images/load.gif" alt="load" />' +
                '</div>');
        };
        $feedContainer.removeLoad = function () {
            $(this).find('.load-gif').remove();
        };


        const loadNews = function () {
            $feedContainer.addLoad();
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                {
                    call: 'plugin_fksnewsfeed',
                    target: 'feed',
                    name: 'local',
                    news_do: 'more',
                    news_stream: stream,

                    news_feed_s: start,
                    news_feed_l: 3,
                    page_id: JSINFO.id
                },
                function (data) {
                    $feedContainer.removeLoad();
                    start += 3;
                    $feedContainer.find('.load').remove();
                    if (data.html.err) {
                        console.error(data.err);
                    }
                    data.html.feeds.forEach(function (d) {

                        $feedContainer.append(d);

                    });
                    if (window.FB) {
                        FB.XFBML.parse($feedContainer[0]);
                    }
                    if (data.html.msg) {

                        $feedContainer.append(data.html.msg);
                    } else {
                        var $moreNews = $(data.html.btn).click(function () {
                            $(this).remove();
                            loadNews();
                        });
                        $feedContainer.append($moreNews);
                    }


                }
                , 'json');
        };

        loadNews();

        $(document).on("click", '.edit-headline', function () {
            $(this).toggleClass('active');
            $(this).siblings('.edit-body').slideToggle();
        });


    });

    $('span[contenteditable="true"]').on("click", function () {
        document.execCommand('selectAll', false, null);
    });

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



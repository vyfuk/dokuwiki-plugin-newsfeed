<?php

use PluginNewsFeed\Model\News;
use PluginNewsFeed\Model\Stream;
use PluginNewsFeed\Syntax\AbstractStream;

class syntax_plugin_fksnewsfeed_carousel extends AbstractStream {

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{news-carousel>.+?}}', $mode, 'plugin_fksnewsfeed_carousel');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        preg_match_all('/([a-z]+)="([^".]*)"/', substr($match, 16, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, [$parameters]];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode !== 'xhtml') {
            return true;
        }
        list(, $match) = $data;
        list($param) = $match;
        $renderer->nocache();

        $stream = new Stream($this->helper->sqlite);
        $stream->findByName($param['stream']);

        $allNews = $stream->getNews();
        if (count($allNews)) {
            $this->renderCarousel($renderer, $allNews, $param);
        }
        return false;
    }

    private function renderCarousel(Doku_Renderer &$renderer, $news, $params) {
        $id = md5(serialize($news) . time());
        $indicators = [];
        $items = [];
        $noFeeds = 5;
        for ($i = 0; $i < $noFeeds; $i++) {
            if (!isset($news[$i])) {
                break;
            };
            /**
             * @var $feed News;
             */
            $feed = $news[$i];
            $indicators[] = '<li data-target="#' . $id . '" data-slide-to="' . $i . '"></li>';
            $items[] = $this->getCarouselItem($feed, !$i);
        }
        $renderer->doc .= '<div id="' . $id . '" class="feed-carousel carousel slide mb-3 hidden-md-down" data-ride="carousel">';

        $this->renderCarouselIndicators($renderer, $indicators);
        $this->renderCarouselItems($renderer, $items);
        $renderer->doc .= '<div style="position: absolute; bottom: 0; right: 0;">';
        $this->renderEditModal($renderer, $params);
        $renderer->doc .= '</div>';
        $renderer->doc .= '</div>';
    }

    private function renderCarouselIndicators(Doku_Renderer &$renderer, array $indicators) {
        $renderer->doc .= '<ol class="carousel-indicators">';
        foreach ($indicators as $indicator) {
            $renderer->doc .= $indicator;
        }
        $renderer->doc .= '</ol>';
    }

    private function renderCarouselItems(Doku_Renderer &$renderer, array $items) {
        $renderer->doc .= '<div class="carousel-inner" role="listbox">';
        foreach ($items as $item) {
            $renderer->doc .= $item;
        }
        $renderer->doc .= '</div>';
    }

    private function getCarouselItem(News $feed, $active = false) {
        $style = '';
        if ($feed->hasImage()) {
            $style .= 'background-image: url(' . ml($feed->getImage(), ['w' => 1200]) . ')';
        }
        $background = 'bg-' . $feed->getCategory() . '-fade ';
        $html = '';
        $html .= '<div class="carousel-item ' . ($feed->hasImage() ? '' : $background) . ($active ? ' active' : '') .
            '" style="' . $style . '">
            <div class="offset-lg-1 col-lg-8 offset-xl-3 col-xl-5">                
      <div class=" jumbotron-inner-container d-block ' . ($feed->getImage() ? $background : '') . '">';
        $html .= $this->getHeadline($feed);
        $html .= $this->getText($feed);
        $html .= $this->getLink($feed);
        $html .= '</div></div></div>';
        return $html;
    }

    private function getText(News $feed) {
        return '<p>' . p_render('xhtml', p_get_instructions($feed->getText()), $info) . '</p>';
    }

    private function getHeadline(News $feed) {
        return '<h1>' . hsc($feed->getTitle()) . '</h1>';
    }

    private function getLink(News $feed) {
        if ($feed->getLinkTitle()) {
            if (preg_match('|^https?://|', $feed->getLinkHref())) {
                $href = hsc($feed->getLinkHref());
            } else {
                $href = wl($feed->getLinkHref(), null, true);
            }
            return '<p><a class="btn btn-outline-secondary" href="' . $href . '">' . $feed->getLinkTitle() . '</a></p>';
        }
        return '';
    }
}

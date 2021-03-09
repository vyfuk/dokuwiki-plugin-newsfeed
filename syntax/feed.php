<?php

use dokuwiki\Extension\SyntaxPlugin;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use dokuwiki\Cache\Cache;

/**
 * Class syntax_plugin_newsfeed_feed
 * @author Michal Červeňák <miso@fykos.cz>
 */
class syntax_plugin_newsfeed_feed extends SyntaxPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function getType(): string {
        return 'substition';
    }

    public function getPType(): string {
        return 'block';
    }

    public function getAllowedTypes(): array {
        return [];
    }

    public function getSort(): int {
        return 24;
    }

    public function connectTo($mode): void {
        $this->Lexer->addSpecialPattern('{{news-feed>.+?}}', $mode, 'plugin_newsfeed_feed');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        preg_match_all('/([a-z-_]+)="([^".]*)"/', substr($match, 12, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, $parameters];
    }

    public function render($format, Doku_Renderer $renderer, $data): bool {
        if ($format !== 'xhtml') {
            return true;
        }

        [$state, $param] = $data;
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                $renderer->nocache();

                $news = $this->helper->serviceNews->getById($param['id']);
                if (is_null($news) || ($param['id'] == 0)) {
                    $renderer->doc .= '<div class="alert alert-danger">' . $this->getLang('news_non_exist') .
                        '</div>';
                    return true;
                }
                $renderer->doc .= $this->getContent($news, $param);

                return false;
        }
        return true;
    }

    /**
     * @param $data ModelNews
     * @param $params array
     * @return string
     */
    private function getContent(ModelNews $data, array $params): string {
        $f = $data->getCacheFile();
        $cache = new Cache($f, '');
        if ($cache->useCache()) {

            $innerHtml = json_decode($cache->retrieveCache());
        } else {
            $innerHtml = $this->helper->renderer->renderContent($data, $params);

            $cache->storeCache(json_encode($innerHtml));
        }
        $formHtml = $this->helper->renderer->renderEditFields($params);
        return $this->helper->renderer->render($innerHtml, $formHtml, $data);
    }
}

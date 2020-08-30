<?php

use dokuwiki\Extension\SyntaxPlugin;
use \PluginNewsFeed\Model\News;

class syntax_plugin_fksnewsfeed_feed extends SyntaxPlugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
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
        $this->Lexer->addSpecialPattern('{{news-feed>.+?}}', $mode, 'plugin_fksnewsfeed_feed');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        preg_match_all('/([a-z-_]+)="([^".]*)"/', substr($match, 12, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, $parameters];
    }

    public function render($mode, Doku_Renderer $renderer, $data): bool {
        if ($mode == 'xhtml') {

            [$state, $param] = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $renderer->nocache();
                    $news = new News($this->helper->sqlite, $param['id']);
                    $news->load();
                    if (is_null($news) || ($param['id'] == 0)) {
                        $renderer->doc .= '<div class="alert alert-danger">' . $this->getLang('news_non_exist') .
                            '</div>';
                        return true;
                    }
                    $renderer->doc .= $this->getContent($news, $param);

                    return false;
                default:
                    return true;
            }
        }
        return false;
    }

    /**
     * @param $data News
     * @param $params array
     * @return string
     */
    private function getContent(News $data, $params): string {
        $f = $data->getCacheFile();
        $cache = new cache($f, '');
        $json = new JSON();
        if ($cache->useCache()) {
            $innerHtml = $json->decode($cache->retrieveCache());
        } else {
            $innerHtml = $this->helper->renderer->renderContent($data, $params);

            $cache->storeCache($json->encode($innerHtml));
        }
        $formHtml = $this->helper->renderer->renderEditFields($params);
        return $this->helper->renderer->render($innerHtml, $formHtml, $data);
    }
}

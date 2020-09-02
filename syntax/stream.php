<?php

use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\AbstractStream;

/**
 * Class syntax_plugin_newsfeed_stream
 * @author Michal Červeňák <miso@fykos.cz>
 */
class syntax_plugin_newsfeed_stream extends AbstractStream {

    public function connectTo($mode): void {
        $this->Lexer->addSpecialPattern('{{news-stream>.+?}}', $mode, 'plugin_newsfeed_stream');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                preg_match_all('/([a-z]+)="([^".]*)"/', substr($match, 14, -2), $matches);
                $parameters = [];
                foreach ($matches[1] as $index => $match) {
                    $parameters[$match] = $matches[2][$index];
                }
                return [$state, [$parameters]];
        }
        return [$state, null];

    }

    public function render($mode, Doku_Renderer $renderer, $data): bool {
        if ($mode !== 'xhtml') {
            return true;
        }
        [$state, $match] = $data;
        switch ($state) {
            case DOKU_LEXER_SPECIAL:
                [$param] = $match;
                $this->renderStream($renderer, $param);
                return false;
        }
        return true;
    }
}

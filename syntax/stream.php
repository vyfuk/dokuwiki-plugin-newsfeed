<?php

use PluginNewsFeed\Syntax\AbstractStream;

class syntax_plugin_fksnewsfeed_stream extends AbstractStream {

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{news-stream>.+?}}', $mode, 'plugin_fksnewsfeed_stream');
    }

    public function handle($match, $state, $pos, \Doku_Handler $handler) {
        preg_match_all('/([a-z]+)="([^".]*)"/', substr($match, 14, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, [$parameters]];
    }

    public function render($mode, \Doku_Renderer $renderer, $data) {
        if ($mode !== 'xhtml') {
            return true;
        }
        list(, $match) = $data;
        list($param) = $match;
        $this->renderStream($renderer, $param);
        return false;
    }
}

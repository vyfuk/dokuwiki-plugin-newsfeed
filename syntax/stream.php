<?php

class syntax_plugin_fksnewsfeed_stream extends DokuWiki_Syntax_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return [];
    }

    public function getSort() {
        return 3;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed-stream>.+?\}\}', $mode, 'plugin_fksnewsfeed_stream');
    }

    public function handle($match, $state) {
        $param = helper_plugin_fkshelper::extractParamtext(substr($match, 21, -2));
        return [$state, [$param]];
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        if ($mode !== 'xhtml') {
            return true;
        }
        list(, $match) = $data;
        list($param) = $match;
        $attributes = [];
        foreach ($param as $key => $value) {
            $attributes['data-' . $key] = $value;
        }
        $renderer->doc .= '<div class="news-feed-stream"><div class="stream row" ' . buildAttributes($attributes) . '></div></div>';
        return false;
    }

}

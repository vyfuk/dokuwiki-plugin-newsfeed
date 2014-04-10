<?php

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeeds_newsauthor extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<newsauthor>.+?</newsauthor>', $mode, 'plugin_fksnewsfeeds_newsauthor');
    }

    //public function postConnect() { $this->Lexer->addExitPattern('</fkstimer>','plugin_fkstimer'); }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        $match = substr($match, 12, -13);
        $newsauthor=preg_split('/-/', $match);
        //$to_page.=$newsauthor[1];
        //$to_page.=$newsauthor[0];
        if($newsauthor[1]=='render'){
        $to_page.="<div class='fksnewsauthor'>";
        $to_page.=p_render("xhtml", p_get_instructions($newsauthor[0]), $info);;
        $to_page.="</div>";
        }else{}
        return array($state, array($to_page));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($to_page) = $match;
            $renderer->doc .= $to_page;
        }
        return false;
    }

}

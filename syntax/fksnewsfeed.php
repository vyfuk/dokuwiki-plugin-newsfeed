<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

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
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        /* @var $par integer */
        global $par;
        $par = substr($match, 14, -2) + 0;
        $to_page.="<div class='fksnewswrapper'>";
        $rendernews = $this->helper->loadnews();
        foreach ($rendernews as $key => $value) {
            $rendernewsbool = preg_split('/-/', $value);
            if ($rendernewsbool[1] == "T") {

                if ($par) {
                    if ($par % 2) {


                        $to_page.=$this->helper->rendernews($rendernewsbool[0], 'fksnewseven');
                    } else {
                        $to_page.=$this->helper->rendernews($rendernewsbool[0], 'fksnewsodd');
                    }


                    $par --;
                } else {
                    break;
                }
            }
        }
        $to_page.="</div>";
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

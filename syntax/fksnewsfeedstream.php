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

class syntax_plugin_fksnewsfeed_fksnewsfeedstream extends DokuWiki_Syntax_Plugin {

    private $helper;
    private $Sdata = array();
    private $to_page = "";

    //array('indic' => array(), 'items' => array(), 'img' => array(), 'html_indic' => '', 'html_items' => '');

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
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed-stream>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeedstream');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {

        $this->Sdata = $this->helper->FKS_helper->extractParamtext(substr($match, 21, -2));


        //$to_page.=$this->renderstreamB();


        return array($state, null);
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, ) = $data;

            $renderer->doc .= '<div class="fksnewswrapper">'
                    . $this->renderstream()
                    . '</div>';
        }
        return false;
    }

    /*
     * © Michal Červeňák
     * 
     * render stream news
     * 
     */

    private function renderstream() {
        //var_dump($this->Sdata);
        foreach ($this->helper->loadstream($this->Sdata['stream']) as $value) {
            if ($this->Sdata['feed']) {
                $to_page.='<div class="';
                if ($this->Sdata['feed'] % 2) {
                    $to_page.= 'fksnewseven';
                } else {
                    $to_page.='fksnewsodd';
                }
                $to_page.= '">';
                $to_page.=p_render("xhtml", p_get_instructions('<fksnewsfeed id=' . $value . '/>'), $info);
                $to_page.='</div>';
                //$to_page.='<div class="fksbetween"><p></p></div>';

                $this->Sdata['feed'] --;
            } else {
                break;
            }
        }
        return $to_page;
    }

    

}

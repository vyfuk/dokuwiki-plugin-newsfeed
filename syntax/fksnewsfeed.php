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

    private $helper;

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
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed\>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {


        $text = str_replace(array("\n", '{{fksnewsfeed>', '}}'), array('', '', ''), $match);
        /** @var id and even this NF $param */
        $param = $this->helper->FKS_helper->extractParamtext($text);



        return array($state, array($param));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {

        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($param) = $match;
            
            $renderer->doc .= $this->rendernews($param);
            $renderer->doc.='<div class="fks_edit" data-id="' . $param["id"] . '"></div>';
           
        }
        return false;
    }

    private function rendernews($param = array()) {

        $ntext = $this->loadnewssimple($param["id"]);
        
        $cleantext = str_replace(array("\n", '<fksnewsfeed', '</fksnewsfeed>'), array('', '', ''), $ntext);
        list($params, $text) = preg_split('/\>/', $cleantext, 2);
        $data = $this->helper->FKS_helper->extractParamtext($params);

        $tpl = io_readFile(wikiFN('system/html/newsfeed_template'));


        foreach ($this->helper->Fields as $k) {

            if ($k == 'text') {
                $tpl = str_replace('@' . $k . '@', p_render('xhtml', p_get_instructions($text), $info), $tpl);
            } elseif ($k == 'newsdate') {
                $tpl = str_replace('@' . $k . '@', $this->newsdate($data[$k]), $tpl);
            } else {
                $tpl = str_replace('@' . $k . '@', $data[$k], $tpl);
            }
        }
       
        if (!isset($param['even'])) {
            $param['even'] = 'fksnewseven';
        }
        return '<div class="' . $param['even'] . '" data-id="' . $param["id"] . '">' . $tpl . '</div>';
    }

    private function newsdate($date) {
        $enmonth = Array('January', 'February', 'March',
            'April', 'May', 'June',
            'July', 'August', 'September',
            'October', 'November', 'December');
        $langmonth = Array(
            $this->getLang('jan'),
            $this->getLang('feb'),
            $this->getLang('mar'),
            $this->getLang('apr'),
            $this->getLang('may'),
            $this->getLang('jun'),
            $this->getLang('jul'),
            $this->getLang('aug'),
            $this->getLang('sep'),
            $this->getLang('oct'),
            $this->getLang('now'),
            $this->getLang('dec')
        );


        return str_replace($enmonth, $langmonth, $date);
    }

    /*
     * load news @i@ and return text
     */

    private function loadnewssimple($id) {
        return io_readFile(metaFN($this->helper->getwikinewsurl($id), ".txt"), false);
    }

}

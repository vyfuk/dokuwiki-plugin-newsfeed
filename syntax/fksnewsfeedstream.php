<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if(!defined('DOKU_INC')){
    die();
}
if(!defined('DOKU_PLUGIN')){
    define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
}
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeedstream extends DokuWiki_Syntax_Plugin {

    private $helper;

    //array('indic' => array(), 'items' => array(), 'img' => array(), 'html_indic' => '', 'html_items' => '');

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
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 3;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed-stream>.+?\}\}',$mode,'plugin_fksnewsfeed_fksnewsfeedstream');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $param = helper_plugin_fkshelper::ExtractParamtext(substr($match,21,-2));
        return array($state,array($param));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        global $INPUT,$ID;
        if($mode !== 'xhtml'){
            return;
        }
        list(,$match) = $data;
        list($param) = $match;
        $atr = array();
        foreach ($param as $key => $value) {
            $atr['data-'.$key] = $value;
        }
        $stream = $param['stream'];

        $renderer->doc .='<noscript><div class="error"><h1>Asi máte vypnutý JavasScript</h1>
            <p>Pro správne fungovaní téjto stránky je potřebné mít zapnutý 
                <a href="http://en.wikipedia.org/wiki/JavaScript">JavaScript</a>.</p>
            <p>Pokud chcete zobrazit tento web plnohodnotně 
                <a href="https://www.google.cz/search?q=how+to+turn+on+javascript">
                    zapněte si JavaScript</a>!</p></div></noscript>';


        $renderer->doc .='<div class="stream-container" data-stream="'.$param['stream'].'">';

        if(auth_quickaclcheck('start') >= $this->getConf('perm_manage')){
            $this->PrintCreateBtn($renderer,$stream);
            $this->PrintPullBtn($renderer,$stream);
            $this->PrintCacheBtn($renderer,$stream);
        }
        if(auth_quickaclcheck($ID) >= $this->getConf('perm_rss')){
            $this->PrintRSS($renderer,$stream);
        }

        $renderer->doc .='<div class="feed-container" '.buildAttributes($atr).'></div></div>';
        return false;
    }

    /**
     * 
     * @param type $r
     * @param type $stream
     */
    private function PrintPullBtn(Doku_Renderer &$renderer,$stream) {
        $form2 = new Doku_Form(array('method' => 'POST','class' => 'info'));
        $form2->addHidden('target','plugin_fksnewsfeed');
        $form2->addHidden('do','admin');
        $form2->addHidden('page','fksnewsfeed_push');
        $form2->addHidden('stream',$stream);
        $form2->addElement(form_makeButton('submit','',$this->getLang('btn_push_stream')));
        ob_start();
        html_form('addnews',$form2);
        $renderer->doc .= ob_get_contents();
        ob_end_clean();   
        
    }

    /**
     * 
     * @param type $r
     * @param type $stream
     */
    private function PrintRSS(Doku_Renderer &$renderer,$stream) {
        $renderer->doc .=html_open_tag('div',array('class' => 'rss'));

        $renderer->doc .='<a href="'.DOKU_URL.'feed.php?stream='.$stream.'"><span class="icon small-btn rss-icon"></span><span class="btn-big">RSS</span></a>';
        $renderer->doc .='<span class="link" contenteditable="true" >'.DOKU_URL.'feed.php?stream='.$stream.'</span>';
        $renderer->doc .='</div>';
    }

    private function PrintCreateBtn(Doku_Renderer &$renderer,$stream) {

        $form3 = new Doku_Form(array('method' => 'GET','class' => 'info'));
        $form3->addHidden('do','edit');
        $form3->addHidden('target','plugin_fksnewsfeed');
        $form3->addHidden('news_do','create');
        $form3->addHidden('news_id',0);
        $form3->addHidden('news_stream',$stream);
        $form3->addElement(form_makeButton('submit','',$this->getLang('btn_create_news')));

        ob_start();
        html_form('create_news',$form3);
        $renderer->doc .=ob_get_contents();
        ob_clean();
    }

    private function PrintCacheBtn(Doku_Renderer &$renderer) {
        ob_start();
        $form3 = new Doku_Form(array('class' => 'warning'));
        $form3->addHidden('fksnewsfeed_purge','true');

        $form3->addElement(form_makeButton('submit',null,$this->getLang('cache_del_full')));
        html_form('cachenews',$form3);
        $renderer->doc.= ob_get_contents();
        ob_clean();
    }

}

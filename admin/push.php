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

require_once(DOKU_PLUGIN.'admin.php');

class admin_plugin_fksnewsfeed_push extends DokuWiki_Admin_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 291;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText() {
        $menutext = 'FKS_newsfeed: push --'.$this->getLang('push_menu');
        return $menutext;
    }

    public function handle() {
        global $INPUT;
        if($INPUT->str('news_do') == 'push_save'){
            $stream = $INPUT->str('stream');
            $news_id = $INPUT->str('news_id');

            $stream_id = $this->helper->streamToID($stream);
            $arrs = array($stream_id);
            if($INPUT->str('all_dependence')){
                $this->helper->FullParentDependence($stream_id,$arrs);
            }

            foreach ($arrs as $arr) {
                $this->helper->SaveIntoStream($arr,$news_id);
            }
            header('Location: '.$_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html() {

        global $INPUT;

        echo '<h1>'.$this->getLang('push_menu').'</h1>';
        echo '<div class="info"><span>'.$this->getLang('push_in_stream').': '.$INPUT->str('stream','').'</span></div>';
        $streams = $this->helper->AllStream();
        $form2 = new Doku_Form(array('method' => 'POST','id'=>'FKS_stream_choose'));
        $form2->addHidden('target','plugin_fksnewsfeed');
        $form2->addHidden('do','admin');
        $form2->addHidden('page','fksnewsfeed_push');
        $s = array();
        foreach ($streams as $stream) {
            $id = $this->helper->streamToID($stream);
            $s[$id] = $stream;
        }

        $form2->addElement(form_makeListboxField('stream',$s,$INPUT->str('stream',''),$this->getLang('stream')));
        $form2->addElement(form_makeButton('submit',null,$this->getLang('push_choose_stream')));
        html_form('stream',$form2);
        if($INPUT->str('stream') == ""){
            
        }else{
            echo '<h2>'.$this->getLang('push_menu').': '.$INPUT->str('stream').'</h2>';
            $stream = $INPUT->str('stream');
            $all_news = $this->helper->AllNewsFeed();
            $news_in_stream = $this->NewsToID($this->helper->LoadStream($stream));
            foreach ($this->NewsToID($all_news) as $id) {
                echo '<div class="FKS_newsfeed push">';



                if(array_search($id,$news_in_stream) === FALSE){
                    $n = str_replace(array('@id@','@even@','@edited@','@stream@','@page_id@'),array($id,'even','false',' ',' '),$this->helper->simple_tpl);
                    echo p_render('xhtml',p_get_instructions($n),$info);

                    $form2 = new Doku_Form(array('method' => 'POST'));

                    $form2->addHidden('do','admin');
                    $form2->addHidden('page','fksnewsfeed_push');
                    $form2->addHidden('news_do','push_save');
                    $form2->addHidden('news_id',$id);
                    $form2->addHidden('stream',$stream);
                    $form2->addElement(form_makeCheckboxField('all_dependence',1,$this->getLang('alw_dep')));

                    $form2->addElement(form_makeButton('submit',null,$this->getLang('btn_push_news').$stream));
                    html_form('stream',$form2);
                    echo '<hr>';
                }else{/*
                      echo '<button disabled>Táto novinka je už v tomto vlákne</button>'; */
                }
                echo'</div>';
                
            }
        }
    }

    public function NewsToID($news) {
        $n = array();
        foreach ($news as $new) {
            $n[] = $new['news_id'];
        }
        return $n;
    }

}

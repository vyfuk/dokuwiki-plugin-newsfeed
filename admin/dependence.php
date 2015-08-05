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

class admin_plugin_fksnewsfeed_dependence extends DokuWiki_Admin_Plugin {

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
        $menutext = 'FKS_newsfeed: Dependence --'.$this->getLang('menu_dependence');
        return $menutext;
    }

    public function handle() {
        global $INPUT;
        $child = $INPUT->str('dep_child');
        $parent = $INPUT->str('dep_parent');
        if($child == "" || $parent == ""){
            return;
        }
        $child_id = $this->helper->StreamToID($child);
        $parent_id = $this->helper->StreamToID($parent);
        $d = $this->helper->AllParentDependence($parent_id);
        if(in_array($child_id,$d)){
            msg('Dependence alredy exist',-1);
        }else{
            $f = $this->helper->CreateDependence($parent_id,$child_id);
            if($f){
                msg('Dependence hes been created',1);
            }
        }
    }

    public function html() {
        global $lang;
        $streams = $this->helper->AllStream();
        ptln('<h1>'.$this->getLang('manage').'</h1>',0);
        ptln('<h2>'.'Create dependence'.'</h2>');
        ptln('<div class="info">Novinka pridaná do rodičovského streamu sa automaticky pridá aj do detského streamu. <br />
                Novinka pridaná do detského streamu sa v rodičovskom nezobrazí.</div>');
        $form = new Doku_Form(array('id' => "create_dependence",
            'method' => 'POST','action' => null));
        $form->addHidden('news_do','stream_dependence');
        $form->addElement(form_makeListboxField('dep_parent',$streams,null,'Stream od ktorého sa dedí "parent"'));
        $form->addElement('<div class="clearer"></div>');
        $form->addElement(form_makeListboxField('dep_child',$streams,null,'Streem do ktorého sa dedí "child"'));
        
        $form->addElement(form_makeButton('submit','',$lang['btn_save'],array()));
        html_form('nic',$form);
        ptln('<h2>Zoznam Streamov</h2>');
        ptln('<ul>');
        foreach ($streams as $stream) {
            $stream_id = $this->helper->StreamToID($stream);
            ptln('<li><h3>Stream: '.$stream.'</h3>');
            $pdep = $this->helper->AllParentDependence($stream_id,'p');
            if(!empty($pdep)){

                ptln('<label>Od tohoto streamu priamo dedí, je parent voči":</label>');
                ptln('<ul>');
                foreach ($pdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }
                ptln('</ul>');
                ptln('<label>Od tohoto streamu celkovo dedí, je parent voči:</label>');
                ptln('<ul>');
                $fpdep = array();
                $this->helper->FullParentDependence($stream_id,$fpdep);
                foreach ($fpdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }
                ptln('</ul>');
            }
            $cdep = $this->helper->AllChildDependence($stream_id,'p');
            if(!empty($cdep)){
                ptln('<label>Tento stream priamo dedí od, je child voči:</label>');
                ptln('<ul>');
                foreach ($cdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }
                ptln('</ul>');
                ptln('<label>Tento streamc celkovo dedí od, je child voči:</label>');
                ptln('<ul>');
                $fcdep = array();
                $this->helper->FullChildDependence($stream_id,$fcdep);
                foreach ($fcdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }


                ptln('</ul>');
            }
            ptln('</li><hr>');
        }
        ptln('</ul>');
    }

}

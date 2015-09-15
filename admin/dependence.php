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
        $menutext = 'FKS_newsfeed: Dependence --'.$this->getLang('dep_menu');
        return $menutext;
    }

    function getTOC() {
        return array(
            array('hid' => 'dep_create','title' => $this->getLang('dep_create')),
            array('hid' => 'dep_list','title' => $this->getLang('dep_list')),
            array('hid' => 'dep_delete','title' => $this->getLang('dep_delete'))
            
        );
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
            msg($this->getLang('dep_exist'),-1);
        }else{
            $f = $this->helper->CreateDependence($parent_id,$child_id);
            if($f){
                msg($this->getLang('dep_created'),1);
            }
        }
    }

    public function html() {
        global $lang;
        $streams = $this->helper->AllStream();
        ptln('<h1>'.$this->getLang('dep_menu').'</h1>',0);
        ptln('<h2 id="dep_create">'.$this->getLang('dep_create').'</h2>');
        ptln('<div class="info">'.$this->getLang('dep_full_info').'</div>');
        $form = new Doku_Form(array('id' => "create_dependence",
            'method' => 'POST','action' => null));
        $form->addHidden('news_do','stream_dependence');
        $form->addElement(form_makeListboxField('dep_parent',$streams,null,$this->getLang('dep_parent_info')));
        $form->addElement('<div class="clearer"></div>');
        $form->addElement(form_makeListboxField('dep_child',$streams,null,$this->getLang('dep_child_info')));

        $form->addElement(form_makeButton('submit','',$lang['btn_save'],array()));
        html_form('nic',$form);
        ptln('<h2 id="dep_list">'.$this->getLang('dep_list').':</h2>');
        ptln('<ul>');
        foreach ($streams as $stream) {
            $stream_id = $this->helper->StreamToID($stream);
            ptln('<li><h3>'.$this->getLang('stream').': '.$stream.'</h3>');
            $pdep = $this->helper->AllParentDependence($stream_id,'p');
            if(!empty($pdep)){

                ptln('<span>'.$this->getLang('dep_list_parent').'</span>');
                ptln('<ul>');
                foreach ($pdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }
                ptln('</ul>');
                ptln('<span>'.$this->getLang('dep_list_parent_full').'</span>');
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
                ptln('<span>'.$this->getLang('dep_list_child').'</span>');
                ptln('<ul>');
                foreach ($cdep as $d) {
                    ptln('<li>'.$this->helper->IDToStream($d).'</li>');
                }
                ptln('</ul>');
                ptln('<span>'.$this->getLang('dep_list_child_full').'</span>');
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

        ptln('<h2 id="dep_delete">'.$this->getLang('dep_delete').'</h2>',5);
        ptln('<div class="level2">',6);
        ptln('<p>',7);
        echo 'Ak chcete zmazať závysloť streamov prosím požite rozhranie <a href="'.DOKU_BASE.'?do=admin&page=sqlite">SQLite</a>.<br />
        
        Pred zmazaním si vyhľdajete <span class="grey">dependece_id</span> pomocou dotazu:';
        ptln('</p>',7);
        /* začiatok dotazu */
        ptln('<div class="code">',7);
        ptln('<span class="blue">SELECT </span>',8);
        ptln('<span class="red">d</span>.<span class="green">dependence_id</span>, ',8);
        ptln('<span class="red">s_parent</span>.<span class="green">name</span> <span class="blue">AS </span><span class="orange">"parent_name"</span>,',8);
        ptln('<span class="red">s_child</span >.<span class="green">name</span> <span class="blue">AS </span><span class="orange">"child_name"</span> ',8);
        ptln('<br />',8);
        ptln('<span class = "blue">FROM</span> <span class = "red">'.helper_plugin_fksnewsfeed::db_table_dependence.' d</span>',8);
        ptln('<span class = "blue">JOIN</span> <span class = "red">'.helper_plugin_fksnewsfeed::db_table_stream.' s_parent</span> <span class = "blue">ON </span><span class = "red">d</span>.<span class = "green">parent</span>=<span class = "red">s_parent</span>.<span class = "green">stream_id </span>',8);
        ptln('<br />',8);
        ptln('<span class = "blue">JOIN</span> <span class = "red">'.helper_plugin_fksnewsfeed::db_table_stream.' s_child</span> <span class = "blue">ON </span><span class = "red">d</span>.<span class = "green">child</span>=<span class = "red">s_child</span>.<span class = "green">stream_id</span>;',8);
        ptln('</div>',7);
        /* koniec dotazu */
        ptln('<p>',7);
        echo 'Následne nahraďte v dotaze <span class = "grey">dependece_id</span> ID závyslosti ktorú chcte zmazať.';
        ptln('</p>',7);
        echo '<div class = "code">
        <span class = "blue">DELETE FROM </span>
        <span class = "red">"fks_newsfeed_dependence" </span>
        <span class = "blue">WHERE </span>
        <span class = "green">dependence_id </span>= <span class = "grey">'.htmlspecialchars('<dependence_id>').'</span>;
        </div>';

        ptln('</div>');
    }

}

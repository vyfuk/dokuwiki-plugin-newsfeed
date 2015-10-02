<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if(!defined('DOKU_INC')){
    die();
}

/** $INPUT 
 * @news_do add/edit/
 * @news_id no news
 * @news_strem name of stream
 * @id news with path same as doku @ID
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */
class action_plugin_fksnewsfeed_form extends DokuWiki_Action_Plugin {

    private static $modFields;
    private static $cartesField = array('email','author');
    private $helper;
   

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
        self::$modFields = helper_plugin_fksnewsfeed::$Fields;
    }

    /**
     * 
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('HTML_EDIT_FORMSELECTION','BEFORE',$this,'form_to_news');
        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'save_news');
        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'update_order_save');
        $controller->register_hook('TPL_ACT_RENDER','BEFORE',$this,'stream_delete');
    }

    /**
     * 
     * @global type $TEXT
     * @global type $INPUT
     * @global type $ID
     * @param Doku_Event $event
     * @param type $param
     * @return type
     */
    public function form_to_news(Doku_Event &$event) {
        global $TEXT;
        global $INPUT;
        if($INPUT->str('target') !== 'plugin_fksnewsfeed'){
            return;
        }
        $event->preventDefault();
        $form = $event->data['form'];

        if(array_key_exists('wikitext',$_POST)){
            foreach ($this->modFields as $field) {
                $data[$field] = $INPUT->param($field);
            }
        }else{
            if($INPUT->int('news_id') != null){
                $data = $this->helper->LoadSimpleNews($INPUT->str("news_id"));
                $TEXT = $data['text'];
            }else{
                list($data,$TEXT) = $this->CreateDefault();
            }
        }

        $form->startFieldset('Newsfeed');
        $form->addHidden('target','plugin_fksnewsfeed');
        $form->addHidden('news_id',$INPUT->str("news_id"));
        $form->addHidden('news_do',$INPUT->str('news_do'));
        $form->addHidden('news_stream',$INPUT->str('news_stream'));
        
        foreach (self::$modFields as $field) {
            if($field == 'text'){
                $value = $INPUT->post->str('wikitext',$data[$field]);
                $form->addElement(html_open_tag('div',array('class' => 'clearer')));
                $form->addElement(html_close_tag('div'));
                $form->addElement(form_makeWikiText($TEXT,array()));
            }elseif($field=='newsdate'){
                $value = $INPUT->post->str($field,$data[$field]);
                 $form->addElement(form_makeField('datetime-local',$field,$value,$this->getLang($field)));

            }elseif($field=='category'){
                $form->addElement(form_makeListboxField($field,array('default','DSEF','TSAF','important'),null,$this->getLang($field)));
            }else {              
                $value = $INPUT->post->str($field,$data[$field]);
                $form->addElement(form_makeTextField($field,$value,$this->getLang($field),$field,null,array('list' => 'news_list_'.$field)));
            }
        }
        foreach (self::$cartesField as $field) {
            $form->addElement(form_makeDataList('news_list_'.$field,$this->helper->AllValues($field)));
        }
        $form->endFieldset();
        
    }

    public function save_news() {
        global $INPUT;
        global $ACT;

        if($INPUT->str("target") == "plugin_fksnewsfeed"){
            global $TEXT;
            global $ID;
            if(isset($_POST['do']['save'])){
                $data = array();
                foreach (self::$modFields as $field) {
                    if($field == 'text'){
                        $data[$field] = cleanText($INPUT->str('wikitext'));
                        unset($_POST['wikitext']);
                    }else{
                        $data[$field] = $INPUT->param($field);
                    }
                }
                if($INPUT->str('news_do') == 'create'){
                    $id = $this->helper->SaveNews($data,$INPUT->str('news_id'),FALSE);
                    $stream_id = $this->helper->StreamToID($INPUT->str('news_stream'));
                    $arrs = array($stream_id);
                    $this->helper->FullParentDependence($stream_id,$arrs);
                    foreach ($arrs as $arr) {
                        $this->helper->SaveIntoStream($arr,$id);
                    }
                }else{
                    $this->helper->SaveNews($data,$INPUT->str('news_id'),true);
                }
                unset($TEXT);
                unset($_POST['wikitext']);
                $ACT = 'show';
                $ID = 'start';
            }
        }
    }

    private function CreateDefault() {
        global $INFO;
        return array(
            array('author' => $INFO['userinfo']['name'],
                'newsdate' => date('Y-m-d\TH:i:s'),
                'email' => $INFO['userinfo']['mail'],
                'text' => $this->getLang('news_text'),
                'name' => $this->getLang('news_name'),
                'image' => '',
                'category'=>''),
            $this->getLang('news_text'));
    }

    /**
     * 
     * @global type $INPUT
     * @global string $ACT
     * @global type $TEXT
     * @global type $ID
     * @global type $INFO
     * @param Doku_Event $event
     * @param type $param
     */
    public function update_order_save() {
        global $INPUT;

        if($INPUT->str("target") == "plugin_fksnewsfeed"){
            if($INPUT->str('news_do') == 'order_save'){
                foreach ($INPUT->param('weight') as $key => $value) {
                    $this->helper->UpdateWeight($value,$key);
                }
                global $ACT;
                $this->helper->CleanOrder();
                $ACT = 'show';
            }
        }
    }

    public function stream_delete(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str("target") != "plugin_fksnewsfeed"){
            return;
        }
        if($INPUT->str('news_do') != 'order'){
            return;
        }
        if(auth_quickaclcheck('start') < $this->getConf('perm_manage')){
            return;
        }
        $event->preventDefault();

        echo '<div class="FKS_newsfeed">';


        ptln('<h1>'.$this->getLang('menu_manage_stream').' <small>stream:'.$INPUT->str('news_stream').'</small></h1>');
        ptln('<h2 id="menu_add_to_stream">'.$this->getLang('menu_add_to_stream').'</h2>');
        ptln('<div class="add_to_stream">');
        $form2 = new Doku_Form(array());
        $form2->addElement(form_makeTextField('weight',0,$this->getLang('weight')));
        $form2->addHidden('news_stream',$INPUT->str('news_stream'));
        $form2->addElement(form_makeTextField('news_id',0,'ID'));
        $form2->addElement(form_makeButton('button',null,$this->getLang('btn_add_to_stream')));
        html_form('add_to_stream',$form2);
        ptln('</div>');


        ptln('<h2 id="menu_change_order">'.$this->getLang('menu_change_order').'</h2>');
        ptln('<p>'.$this->getLang('info_change_order').'</p>');
        $form = new Doku_Form(array('id' => "save",
            'method' => 'POST','action' => null));

        $form->addHidden('news_stream',$INPUT->str('news_stream'));
        $form->addHidden("target","plugin_fksnewsfeed");
        $form->addHidden('news_do','order_save');
        $form->addElement(form_makeButton('submit','',$this->getLang('btn_change_order'),array()));
        $form->addElement(html_open_tag('div',array('class' => 'order_stream')));

        foreach ($this->helper->loadstream($INPUT->str('news_stream'),true) as $key => $value) {
            $news_id = $value['news_id'];
            $order_id = $value['order_id'];
            $weight = $value['weight'];
            $news = $this->helper->create_order_div($news_id,$order_id,$weight,$key);
            $form->addElement($news);
        }
        $form->addElement('</div>');
        html_form('nic',$form);

        ptln('</div>');
    }

}

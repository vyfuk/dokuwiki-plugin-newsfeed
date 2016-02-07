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

    protected $modFiels;
    private $cartesField = array('email','author');
    private $helper;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    
    public function __construct() {
        
        $this->helper = $this->loadHelper('fksnewsfeed');
        $this->modFields = $this->helper->Fields;
     
      
    }

    /**
     * 
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('HTML_EDIT_FORMSELECTION','BEFORE',$this,'form_to_news');
        
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
       
        foreach ($this->modFields as $field) {
            if($field == 'text'){
                $value = $INPUT->post->str('wikitext',$data[$field]);
                $form->addElement(html_open_tag('div',array('class' => 'clearer')));
                $form->addElement(html_close_tag('div'));
                $form->addElement(form_makeWikiText($TEXT,array()));
            }elseif($field == 'newsdate'){
                $value = $INPUT->post->str($field,$data[$field]);
                $form->addElement(form_makeField('datetime-local',$field,$value,$this->getLang($field),null,null,array('step' => 1)));
            }elseif($field == 'category'){
                $value = $INPUT->post->str($field,$data[$field]);
                $form->addElement(form_makeListboxField($field,array('default','DSEF','TSAF','important'),$value,$this->getLang($field)));
            }else{
                $value = $INPUT->post->str($field,$data[$field]);
                $form->addElement(form_makeTextField($field,$value,$this->getLang($field),$field,null,array('list' => 'news_list_'.$field)));
            }
        }
        foreach ($this->cartesField as $field) {
            $form->addElement(form_makeDataList('news_list_'.$field,$this->helper->AllValues($field)));
        }
        $form->endFieldset();
    }
    /**
     * 
     * @global type $INFO
     * @return type
     */
    private function CreateDefault() {
        global $INFO;
        return array(
            array('author' => $INFO['userinfo']['name'],
                'newsdate' => date('Y-m-d\TH:i:s'),
                'email' => $INFO['userinfo']['mail'],
                'text' => $this->getLang('news_text'),
                'name' => $this->getLang('news_name'),
                'image' => '',
                'category' => ''),
            $this->getLang('news_text'));
    } 
}

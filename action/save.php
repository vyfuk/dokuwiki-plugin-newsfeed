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
class action_plugin_fksnewsfeed_save extends DokuWiki_Action_Plugin {

    private static $modFields;
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

        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'SaveNews');
        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'SavePriority');
        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'SaveDelete');
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
    public function SavePriority(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str('target') !== 'plugin_fksnewsfeed'){
            return;
        }
        if($INPUT->str('news_do') !== 'priority'){
            return;
        }
        if(auth_quickaclcheck('start') < AUTH_EDIT){
            return;
        }

        $f = $this->helper->getCasheFile($INPUT->str('news_id'),$INPUT->str('news_stream'),'true','default');

        $cache = new cache($f,'');
        $cache->removeCache();

        $stream_id = $this->helper->streamToID($INPUT->str('news_stream'));

        if($this->helper->SavePriority($INPUT->str('news_id'),$stream_id,floor($INPUT->str('priority')),$INPUT->str('priority_form'),$INPUT->str('priority_to'))){
            header('Location: '.$_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function SaveNews() {
        global $INPUT;
        global $ACT;

        if($INPUT->str("target") == "plugin_fksnewsfeed"){
            global $TEXT;
            global $ID;
            if(isset($_POST['do']['save'])){
                $f = $this->helper->getCasheFile($INPUT->str('news_id'));
                $cache = new cache($f,'');
                $cache->removeCache();

                



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

    public function SaveDelete(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str("target") != "plugin_fksnewsfeed"){
            return;
        }
        if($INPUT->str('news_do') != 'delete_save'){
            return;
        }
        if(auth_quickaclcheck('start') < $this->getConf('perm_manage')){
            return;
        }


        $stream_id = $this->helper->streamTOID($INPUT->str('stream'));

        $this->helper->DeleteOrder($INPUT->str('news_id'),$stream_id);
        header('Location: '.$_SERVER['REQUEST_URI']);
        exit();
    }

}

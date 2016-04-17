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
class action_plugin_fksnewsfeed_token extends DokuWiki_Action_Plugin {

    private $helper;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     * 
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS','AFTER',$this,'AddFBmeta');
    }
    public function AddFBmeta(Doku_Event &$event) {
        global $ID;

        global $INPUT;

        if(!$INPUT->str('fksnews_id')){
            return;
        }
        
        
        $news_id = $INPUT->str('fksnews_id');
        $news = $this->helper->LoadSimpleNews($news_id);


        $this->helper->social->meta->AddMetaData('og','title',$news['name']);
        $this->helper->social->meta->AddMetaData('og','url',$this->helper->GetToken($news_id,$ID));
        $text = p_render('text',p_get_instructions($news['text']),$info);
        $this->helper->social->meta->AddMetaData('og','description',$text);

        if($news['image'] != ""){
            $this->helper->social->meta->AddMetaData('og','image',ml($news['image'],null,true,'&',true));
        }
       
    }

}

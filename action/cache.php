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
class action_plugin_fksnewsfeed_cache extends DokuWiki_Action_Plugin {

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

        $controller->register_hook('ACTION_ACT_PREPROCESS','BEFORE',$this,'DeleteCache');
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
    public function DeleteCache(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str('fksnewsfeed_purge') !== 'true'){
            return;
        }
        if($INPUT->str('news_id') == ''){
            $news = $this->helper->AllNewsFeed();
            foreach ($news as $new) {
                $f = $this->helper->getCacheFile($new['news_id']);
                $cache = new cache($f,'');
                $cache->removeCache();
            }
        }else{
            $f = $this->helper->getCacheFile($INPUT->str('news_id'));
            $cache = new cache($f,'');
            $cache->removeCache();
        }
    }

}

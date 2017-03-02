<?php

class action_plugin_fksnewsfeed_cache extends DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'deleteCache');
    }

    public function deleteCache() {
        global $INPUT;
        if ($INPUT->str('fksnewsfeed_purge') !== 'true') {
            return true;
        }
        if ($INPUT->str('news_id') == '') {
            $news = $this->helper->allNewsFeed();
            foreach ($news as $new) {
                $f = $this->helper->getCacheFile($new['news_id']);
                $cache = new cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = $this->helper->getCacheFile($INPUT->str('news_id'));
            $cache = new cache($f, '');
            $cache->removeCache();
        }
        return false;
    }

}

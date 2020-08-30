<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use \PluginNewsFeed\Model\News;

class action_plugin_fksnewsfeed_token extends ActionPlugin {

    private helper_plugin_fksnewsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     *
     * @param EventHandler $controller
     */
    public function register(EventHandler $controller): void {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'addFBMeta');
    }

    public function addFBMeta(): void {
        global $ID;
        global $INPUT;
        if (!$INPUT->str('news-id')) {
            return;
        }
        $news_id = $INPUT->str('news-id');
        $news = new News($this->helper->sqlite, $news_id);
        $news->load();

        $this->helper->openGraphData->addMetaData('og', 'title', $news->getTitle());
        $this->helper->openGraphData->addMetaData('og', 'url', $news->getToken($ID));
        $text = p_render('text', p_get_instructions($news->getText()), $info);
        $this->helper->openGraphData->addMetaData('og', 'description', $text);
        if ($news->hasImage()) {
            $this->helper->openGraphData->addMetaData('og', 'image', ml($news->getImage(), null, true, '&', true));
        }
    }
}

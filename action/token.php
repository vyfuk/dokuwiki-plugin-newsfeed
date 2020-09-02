<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model\News;

/**
 * Class action_plugin_newsfeed_token
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed_token extends ActionPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
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

        $this->helper->getOpenGraphData()->addMetaData('og', 'title', $news->getTitle());
        $this->helper->getOpenGraphData()->addMetaData('og', 'url', $news->getToken($ID));
        $text = p_render('text', p_get_instructions($news->getText()), $info);
        $this->helper->getOpenGraphData()->addMetaData('og', 'description', $text);
        if ($news->hasImage()) {
            $this->helper->getOpenGraphData()->addMetaData('og', 'image', ml($news->getImage(), null, true, '&', true));
        }
    }
}

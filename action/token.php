<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;

/**
 * Class action_plugin_newsfeed_token
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed_token extends ActionPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function register(EventHandler $controller): void {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'addFBMeta');
    }

    public function addFBMeta(): void {
        global $ID;
        global $INPUT;
        if (!$INPUT->str('news-id')) {
            return;
        }

        $newsId = $INPUT->str('news-id');
        $news = $this->helper->serviceNews->getById($newsId);

        $this->helper->openGraphData->addMetaData('og', 'title', $news->title);
        $this->helper->openGraphData->addMetaData('og', 'url', $news->getToken($ID));
        $text = $news->renderText();
        $this->helper->openGraphData->addMetaData('og', 'description', $text);
        if ($news->hasImage()) {
            $this->helper->openGraphData->addMetaData('og', 'image', ml($news->image, null, true, '&', true));
        }
    }
}

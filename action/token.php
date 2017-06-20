<?php

class action_plugin_fksnewsfeed_token extends DokuWiki_Action_Plugin {

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
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'addFBMeta');
    }

    public function addFBMeta() {
        global $ID;
        global $INPUT;
        if (!$INPUT->str('news-id')) {
            return;
        }
        $news_id = $INPUT->str('news-id');
        $news = $this->helper->loadSimpleNews($news_id);

        $this->helper->social->meta->addMetaData('og', 'title', $news->getTitle());
        $this->helper->social->meta->addMetaData('og', 'url', $this->helper->getToken($news_id, $ID));
        $text = p_render('text', p_get_instructions($news->getText()), $info);
        $this->helper->social->meta->addMetaData('og', 'description', $text);
        if ($news->hasImage()) {
            $this->helper->social->meta->addMetaData('og', 'image', ml($news->getImage(), null, true, '&', true));
        }
    }
}

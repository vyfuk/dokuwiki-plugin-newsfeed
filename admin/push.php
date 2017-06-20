<?php

use \dokuwiki\Form\Form;

// TODO to action component

class admin_plugin_fksnewsfeed_push extends DokuWiki_Admin_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 291;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText() {
        return 'News feed push --' . $this->getLang('push_menu');
    }

    public function handle() {
        global $INPUT;
        if (!checkSecurityToken()) {
            return;
        };
        $stream = $INPUT->param('news')['stream'];
        $newsID = $INPUT->param('news')['id'];

        if ($stream && $newsID) {
            $targetStreamID = $this->helper->streamToID($stream);
            $streamIDs = [$targetStreamID];
            if ($INPUT->str('all_dependence')) {
                $this->helper->fullParentDependence($targetStreamID, $streamIDs);
            }

            foreach ($streamIDs as $streamID) {
                $priority = new \PluginNewsFeed\Priority(null, $newsID, $streamID);
                $priority->create();
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html() {
        global $INPUT;
        $stream = $INPUT->param('news')['stream'];
        echo '<h1>' . $this->getLang('push_menu') . '</h1>';
        echo '<div class="info">' . $this->getLang('push_in_stream') . ': ' . $stream . '</div>';

        $streams = $this->helper->allStream();
        echo $this->getChangeStreamForm($streams)->toHTML();

        if ($stream) {
            echo '<h2>' . $this->getLang('push_menu') . ': ' . $stream . '</h2>';
            $newsInStream = $this->newsToID($this->helper->loadStream($stream));
            $allNews = $this->helper->allNewsFeed();

            foreach ($this->newsToID($allNews) as $id) {
                if (array_search($id, $newsInStream) === FALSE) {
                    echo $this->helper->printNews($id, 'even', ' ', ' ', false);
                    echo $this->newsAddForm($stream, $id);
                    echo '<hr class="clearfix">';
                    tpl_flush();
                }
            }
        }
    }

    /**
     * @param $news \PluginNewsFeed\News[]
     * @return integer[]
     */
    private function newsToID($news) {
        return array_map(function (\PluginNewsFeed\News $value) {
            return $value->getNewsID();
        }, $news);
    }

    /**
     * @param $streamValues array
     * @return Form
     *
     */
    private function getChangeStreamForm(array $streamValues = []) {
        $form = new Form();
        $form->addDropdown('news[stream]', $streamValues, $this->getLang('stream'));
        $form->addButton('submit', $this->getLang('push_choose_stream'));
        return $form;
    }

    private function newsAddForm($stream, $newsID) {
        $newsForm = new Form();
        //$newsForm->setHiddenField('news[do]', 'push_save');
        $newsForm->setHiddenField('do', 'admin');
        $newsForm->setHiddenField('page', 'fksnewsfeed_push');
        $newsForm->setHiddenField('news[id]', $newsID);
        $newsForm->setHiddenField('news[stream]', $stream);
        $newsForm->addCheckbox('all_dependence', $this->getLang('alw_dep'));
        $newsForm->addButton('submit', $this->getLang('btn_push_news') . $stream);
        return $newsForm->toHTML();
    }
}

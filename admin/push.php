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
                $this->helper->saveIntoStream($streamID, $newsID);
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html() {
        global $INPUT;
        $stream = $INPUT->param('news')['stream'];

        echo '<h1>' . $this->getLang('push_menu') . '</h1>';
        echo '<div class="info"><span>' . $this->getLang('push_in_stream') . ': ' . $stream . '</span></div>';

        $streams = $this->helper->allStream();
        $streamValues = [];
        foreach ($streams as $stream) {
            $id = $this->helper->streamToID($stream);
            $streamValues[$id] = $stream;
        }

        echo $this->streamChangeForm($streamValues);

        if ($stream) {
            echo '<h2>' . $this->getLang('push_menu') . ': ' . $stream . '</h2>';
            $newsInStream = $this->newsToID($this->helper->loadStream($stream));
            $allNews = $this->helper->allNewsFeed();

            foreach ($this->newsToID($allNews) as $id) {
                echo '<div class="FKS_newsfeed push">';

                if (array_search($id, $newsInStream) === FALSE) {
                    echo $this->helper->printNews($id, 'even', ' ', ' ', false);
                    echo $this->newsAddForm($stream, $id);
                }
                echo '<hr>';
                echo '</div>';
            }
        }
    }

    private function newsToID($news) {
        return array_map(function ($value) {
            return $value['news_id'];
        }, $news);
    }

    private function streamChangeForm($streamValues) {
        $form = new Form();
        $form->addDropdown('news[stream]', $streamValues, $this->getLang('stream'));
        $form->addButton('submit', $this->getLang('push_choose_stream'));
        return $form->toHTML();
    }

    private function newsAddForm($stream, $newsID) {
        $newsForm = new Form();
        //$newsForm->setHiddenField('news[do]', 'push_save');
        $newsForm->setHiddenField('news[id]', $newsID);
        $newsForm->setHiddenField('news[stream]', $stream);
        $newsForm->addCheckbox('all_dependence', $this->getLang('alw_dep'));
        $newsForm->addButton('submit', $this->getLang('btn_push_news') . $stream);
        return $newsForm->toHTML();
    }
}

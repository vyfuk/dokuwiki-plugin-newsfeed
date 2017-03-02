<?php

use \dokuwiki\Form\Form;

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
        $menuText = 'FKS_newsfeed: push --' . $this->getLang('push_menu');
        return $menuText;
    }

    public function handle() {
        global $INPUT;
        if ($INPUT->str('news_do') === 'push_save') {
            $stream = $INPUT->str('stream');
            $news_id = $INPUT->str('news_id');

            $stream_id = $this->helper->streamToID($stream);
            $arrs = [$stream_id];
            if ($INPUT->str('all_dependence')) {
                $this->helper->fullParentDependence($stream_id, $arrs);
            }

            foreach ($arrs as $arr) {
                $this->helper->saveIntoStream($arr, $news_id);
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html() {
        global $INPUT;

        echo '<h1>' . $this->getLang('push_menu') . '</h1>';
        echo '<div class="info"><span>' . $this->getLang('push_in_stream') . ': ' . $INPUT->str('stream', '') . '</span></div>';

        $streams = $this->helper->allStream();
        $streamValues = [];
        foreach ($streams as $stream) {
            $id = $this->helper->streamToID($stream);
            $streamValues[$id] = $stream;
        }

        echo $this->streamChangeForm($streamValues);

        $stream = $INPUT->str('stream');

        if ($stream) {
            echo '<h2>' . $this->getLang('push_menu') . ': ' . $INPUT->str('stream') . '</h2>';
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

    public function newsToID($news) {
        return array_map(function ($value) {
            return $value['news_id'];
        }, $news);
    }

    private function streamChangeForm($streamValues) {
        $form = new Form();
        $form->setHiddenField('target', 'plugin_fksnewsfeed');
        $form->addDropdown('stream', $streamValues, $this->getLang('stream'));
        $form->addButton('submit', $this->getLang('push_choose_stream'));
        return $form->toHTML();
    }

    private function newsAddForm($stream, $newsID) {
        $newsForm = new Form();
        $newsForm->setHiddenField('news_do', 'push_save');
        $newsForm->setHiddenField('news_id', $newsID);
        $newsForm->setHiddenField('stream', $stream);
        $newsForm->addCheckbox('all_dependence', $this->getLang('alw_dep'));
        $newsForm->addButton('submit', $this->getLang('btn_push_news') . $stream);
        return $newsForm->toHTML();
    }
}

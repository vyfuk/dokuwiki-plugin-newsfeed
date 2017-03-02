<?php

class action_plugin_fksnewsfeed_save extends DokuWiki_Action_Plugin {

    private $modFields;
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
        $this->modFields = $this->helper->Fields;
    }

    /**
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {

        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'saveNews');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'savePriority');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'saveDelete');
    }

    public function savePriority() {
        global $INPUT;
        if ($INPUT->str('target') !== 'plugin_fksnewsfeed') {
            return;
        }
        if ($INPUT->str('news_do') !== 'priority') {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }

        $file = $this->helper->getCacheFile($INPUT->str('news_id'));

        $cache = new cache($file, '');
        $cache->removeCache();

        $stream_id = $this->helper->streamToID($INPUT->str('news_stream'));

        if ($this->helper->savePriority($INPUT->str('news_id'), $stream_id, floor($INPUT->str('priority')), $INPUT->str('priority_form'), $INPUT->str('priority_to'))) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function saveNews() {
        global $INPUT;
        if ($INPUT->str("target") != 'plugin_fksnewsfeed') {
            return;
        }
        global $ACT;
        global $TEXT;
        global $ID;
        if (isset($_POST['do']['save'])) {
            $file = $this->helper->getCacheFile($INPUT->str('news_id'));
            $cache = new cache($file, '');
            $cache->removeCache();

            $data = [];
            foreach ($this->modFields as $field) {
                if ($field === 'text') {
                    $data[$field] = cleanText($INPUT->str('wikitext'));
                    unset($_POST['wikitext']);
                } else {
                    $data[$field] = $INPUT->param($field);
                }
            }
            if ($INPUT->str('news_do') === 'create') {
                $id = $this->helper->saveNews($data, $INPUT->str('news_id'), FALSE);
                $stream_id = $this->helper->streamToID($INPUT->str('news_stream'));
                $streams = [$stream_id];
                $this->helper->fullParentDependence($stream_id, $streams);
                foreach ($streams as $stream) {
                    $this->helper->saveIntoStream($stream, $id);
                }
            } else {
                $this->helper->saveNews($data, $INPUT->str('news_id'), true);
            }
            unset($TEXT);
            unset($_POST['wikitext']);
            $ACT = 'show';
            $ID = $INPUT->str('page_id');
        }
    }

    public function saveDelete() {
        global $INPUT;
        if ($INPUT->str("target") !== 'plugin_fksnewsfeed') {
            return;
        }
        if ($INPUT->str('news_do') !== 'delete_save') {
            return;
        }
        if (auth_quickaclcheck('start') < $this->getConf('perm_manage')) {
            return;
        }
        $stream_id = $this->helper->streamToID($INPUT->str('stream'));
        $this->helper->deleteOrder($INPUT->str('news_id'), $stream_id);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

<?php

class action_plugin_fksnewsfeed_preprocess extends DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'actPreprocess');
    }

    public function actPreprocess(Doku_Event &$event) {
        global $INPUT;
        if ($event->data !== helper_plugin_fksnewsfeed::FORM_TARGET) {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('news')['do']) {
            case 'create':
            case 'edit':
                return;
            case'save':
                $this->saveNews($event);
                return;
            case'priority':
                $this->savePriority($event);
                return;
            case'delete':
                $this->saveDelete($event);
                return;
            case'purge':
                $this->deleteCache($event);
                return;
            default:
                return;
        }
    }

    private function saveNews(Doku_Event &$event) {
        global $INPUT;

        $file = $this->helper->getCacheFile($INPUT->param('news')['id']);
        $cache = new cache($file, '');
        $cache->removeCache();

        $data = [];
        foreach (helper_plugin_fksnewsfeed::$fields as $field) {
            if ($field === 'text') {
                $data[$field] = cleanText($INPUT->str('text'));
            } else {
                $data[$field] = $INPUT->param($field);
            }
        }
        if ($INPUT->param('news')['id'] == 0) {
            $newsID = $this->helper->saveNews($data, $INPUT->param('news')['id'], FALSE);

            $this->saveIntoStreams($newsID);
        } else {
            $this->helper->saveNews($data, $INPUT->param('news')['id'], true);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function saveIntoStreams($newsID) {
        global $INPUT;
        $streamID = $this->helper->streamToID($INPUT->param('news')['stream']);
        $streams = [$streamID];
        $this->helper->fullParentDependence($streamID, $streams);
        foreach ($streams as $stream) {
            $this->helper->saveIntoStream($stream, $newsID);
        }
    }

    private function savePriority(Doku_Event &$event) {
        global $INPUT;
        $file = $this->helper->getCacheFile($INPUT->param('news')['id']);

        $cache = new cache($file, '');
        $cache->removeCache();

        $stream_id = $this->helper->streamToID($INPUT->param('news')['stream']);
        $priority = $INPUT->param('priority');
        if ($this->helper->savePriority($INPUT->param('news')['id'], $stream_id, floor($priority['value']), $priority['from'], $priority['to'])) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    private function saveDelete(Doku_Event &$event) {
        global $INPUT;
        $stream_id = $this->helper->streamToID($INPUT->param('news')['stream']);
        $this->helper->deleteOrder($INPUT->param('news')['id'], $stream_id);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function deleteCache(Doku_Event &$event) {
        global $INPUT;
        if (!$INPUT->param('news')['id']) {
            $news = $this->helper->allNewsFeed();
            foreach ($news as $new) {
                $f = $this->helper->getCacheFile($new['news_id']);
                $cache = new cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = $this->helper->getCacheFile($INPUT->param('news')['id']);
            $cache = new cache($f, '');
            $cache->removeCache();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

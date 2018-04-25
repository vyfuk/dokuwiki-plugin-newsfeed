<?php

use \PluginNewsFeed\Model\Priority;
use \PluginNewsFeed\Model\News;
use \PluginNewsFeed\Model\Stream;

class action_plugin_fksnewsfeed_preprocess extends \DokuWiki_Action_Plugin {

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
                $this->saveNews();
                return;
            case'priority':
                $this->savePriority();
                return;
            case'delete':
                $this->saveDelete();
                return;
            case'purge':
                $this->deleteCache();
                return;
            default:
                return;
        }
    }

    private function saveNews() {
        global $INPUT;

        $file = News::getCacheFileById($INPUT->param('news')['id']);
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
        $news = new News($this->helper->sqlite);
        $news->setTitle($data['title']);
        $news->setAuthorName($data['authorName']);
        $news->setAuthorEmail($data['authorEmail']);
        $news->setText($data['text']);
        $news->setNewsDate($data['newsDate']);
        $news->setImage($data['image']);
        $news->setCategory($data['category']);
        $news->setLinkHref($data['link-href']);
        $news->setLinkTitle($data['linkTitle']);
        if ($INPUT->param('news')['id'] == 0) {
            $newsId = $news->create();
            $this->saveIntoStreams($newsId);
        } else {
            $news->setNewsId($INPUT->param('news')['id']);
            $news->update();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function saveIntoStreams($newsId) {
        global $INPUT;
        $stream = new Stream($this->helper->sqlite, null);
        $stream->findByName($INPUT->param('news')['stream']);
        $streamId = $stream->getStreamId();

        $streams = [$streamId];
        $this->helper->fullParentDependence($streamId, $streams);
        foreach ($streams as $stream) {
            $priority = new Priority($this->helper->sqlite, null, $newsId, $stream);
            $priority->create();
        }
    }

    private function savePriority() {
        global $INPUT;
        $file = News::getCacheFileById($INPUT->param('news')['id']);

        $cache = new cache($file, '');
        $cache->removeCache();
        $stream = new Stream($this->helper->sqlite, null);
        $stream->findByName($INPUT->param('news')['stream']);
        $streamId = $stream->getStreamId();

        $priority = new Priority($this->helper->sqlite, null, $INPUT->param('news')['id'], $streamId);
        $data = $INPUT->param('priority');
        $priority->setPriorityFrom($data['from']);
        $priority->setPriorityTo($data['to']);
        $priority->setPriorityValue($data['value']);
        $priority->checkValidity();
        if ($priority->update()) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    private function saveDelete() {
        global $INPUT;
        $stream = new Stream($this->helper->sqlite, null);
        $stream->findByName($INPUT->param('news')['stream']);
        $streamId = $stream->getStreamId();
        $priority = new Priority($this->helper->sqlite, null, $INPUT->param('news')['id'], $streamId);
        $priority->delete();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function deleteCache() {
        global $INPUT;
        if (!$INPUT->param('news')['id']) {
            $news = $this->helper->allNewsFeed();
            foreach ($news as $new) {
                $f = $new->getCacheFile();
                $cache = new cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = News::getCacheFileById($INPUT->param('news')['id']);
            $cache = new cache($f, '');
            $cache->removeCache();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

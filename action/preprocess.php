<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelPriority;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelStream;
use dokuwiki\Cache\Cache;

/**
 * Class action_plugin_newsfeed_preprocess
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed_preprocess extends ActionPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function register(EventHandler $controller): void {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'actPreprocess');
    }

    public function actPreprocess(Event $event): void {
        global $INPUT;
        if ($event->data !== helper_plugin_newsfeed::FORM_TARGET) {
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
            default:
                return;
            case'save':
                $this->handleNews();
                return;
            case'priority':
                $this->handlePriority();
                return;
            case'delete':
                $this->handleDelete();
                return;
            case'purge':
                $this->handleCache();
                return;
        }
    }

    private function handleNews(): void {
        global $INPUT;
        $file = ModelNews::getCacheFileById($INPUT->param('news')['id']);
        $cache = new Cache($file, '');
        $cache->removeCache();

        $data = [];
        foreach (helper_plugin_newsfeed::$fields as $field) {
            if ($field === 'text') {
                $data[$field] = cleanText($INPUT->str('text'));
            } else {
                $data[$field] = $INPUT->param($field);
            }
        }
        $data = [
            'title' => $data['title'],
            'author_name' => $data['authorName'],
            'author_email' => $data['authorEmail'],
            'text' => $data['text'],
            'news_date' => $data['newsDate'],
            'image' => $data['image'],
            'category' => $data['category'],
            'link_href' => $data['linkHref'],
            'link_title' => $data['linkTitle'],
        ];
        if ($INPUT->param('news')['id'] == 0) {
            $this->helper->serviceNews->create($data);
            $this->saveIntoStreams($this->helper->serviceNews->getMaxId());
        } else {
            $news = $this->helper->serviceNews->getById($INPUT->param('news')['id']);
            $this->helper->serviceNews->update($news, $data);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function saveIntoStreams($newsId) {
        global $INPUT;
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);

        $streams = [$stream->streamId];
        $this->helper->fullParentDependence($stream->streamId, $streams);
        foreach ($streams as $stream) {
            $priority = new ModelPriority($this->helper->sqlite, null, $newsId, $stream);
            $priority->create();
        }
    }

    private function handlePriority(): void {
        global $INPUT;
        $file = ModelNews::getCacheFileById($INPUT->param('news')['id']);

        $cache = new cache($file, '');
        $cache->removeCache();
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);

        $priority = $this->helper->servicePriority->findByNewsAndStream($INPUT->param('news')['id'], $stream->streamId);
        $data = $INPUT->param('priority');
        $this->helper->servicePriority->update($priority, [
            'priority_from' => $data['from'],
            'priority_to' => $data['to'],
            'priority' => $data['value'],
        ]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function handleDelete(): void {
        global $INPUT;
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);
        $priority = $this->helper->servicePriority->findByNewsAndStream($INPUT->param('news')['id'], $stream->streamId);
        $priority->delete();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function handleCache(): void {
        global $INPUT;
        if (!$INPUT->param('news')['id']) {
            $news = $this->helper->serviceNews->getAll();
            foreach ($news as $new) {
                $f = $new->getCacheFile();
                $cache = new cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = ModelNews::getCacheFileById($INPUT->param('news')['id']);
            $cache = new cache($f, '');
            $cache->removeCache();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

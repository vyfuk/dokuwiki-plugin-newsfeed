<?php

use PluginNewsFeed\Model\News;
use PluginNewsFeed\Model\Stream;

class action_plugin_fksnewsfeed_ajax extends \DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'stream');
    }

    public function stream(Doku_Event &$event) {
        global $INPUT;
        if ($INPUT->str('target') !== 'feed') {
            return;
        }
        header('Content-Type: application/json');
        $event->stopPropagation();
        $event->preventDefault();

        $stream = new Stream($this->helper->sqlite, null);
        $stream->findByName($INPUT->param('news')['stream']);
        $news = $stream->getNews();
        $data = $this->printStream($news, (int)$INPUT->param('news')['start'], (int)$INPUT->param('news')['length'], $INPUT->param('news')['stream'], $INPUT->str('page_id'));
        $json = new JSON();

        echo $json->encode($data);
    }

    /**
     * @param $news News[]
     * @param int $start
     * @param int $length
     * @param string $stream
     * @param string $page_id
     * @return array
     */
    private function printStream($news, $start = 0, $length = 5, $stream = "", $page_id = "") {
        $htmlNews = [];
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = $i % 2 ? 'even' : 'odd';
            $htmlNews[] = $news[$i]->render($e, $stream, $page_id);
        }
        return ['html' => ['news' => $htmlNews]];
    }
}

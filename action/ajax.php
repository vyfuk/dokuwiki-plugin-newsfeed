<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model\News;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model\Stream;

/**
 * Class action_plugin_newsfeed_ajax
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed_ajax extends ActionPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function register(EventHandler $controller): void {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'stream');
    }

    public function stream(Event $event): void {
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

        echo json_encode($data);
    }

    /**
     * @param $news News[]
     * @param int $start
     * @param int $length
     * @param string $stream
     * @param string $pageId
     * @return array
     */
    private function printStream(array $news, int $start = 0, int $length = 5, string $stream = '', string $pageId = ''): array {
        $htmlNews = [];
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = $i % 2 ? 'even' : 'odd';
            $htmlNews[] = $news[$i]->render($e, $stream, $pageId);
        }
        return ['html' => ['news' => $htmlNews]];
    }
}

<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelStream;

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
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleStream');
    }

    public function handleStream(Event $event): void {
        $payload = json_decode(file_get_contents('php://input'), true);
        if ($payload['target'] !== 'feed') {
            return;
        }
       // header('Content-Type: application/json');
        $event->stopPropagation();
        $event->preventDefault();

        echo json_encode($this->printStream((int)$payload['news']['offset'], (int)$payload['news']['length'], $payload['news']['stream'], $payload['page_id']));
    }

    /**
     * @param int $start
     * @param int $length
     * @param string $streamId
     * @param string $pageId
     * @return array
     */
    private function printStream(int $start, int $length, string $streamId, string $pageId): array {

        $stream = $this->helper->serviceStream->findByName($streamId);

        $news = $stream->getNews();
        $htmlNews = [];
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = $i % 2 ? 'even' : 'odd';
            $htmlNews[] = $news[$i]->render($e, $streamId, $pageId);
        }
        return ['html' => ['news' => $htmlNews]];
    }
}

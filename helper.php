<?php

require_once DOKU_PLUGIN . 'newsfeed/inc/model/AbstractModel.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/model/News.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/model/Priority.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/model/Stream.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/model/Dependence.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/renderer/AbstractRenderer.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/renderer/VyfukRenderer.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/renderer/FykosRenderer.php';
require_once DOKU_PLUGIN . 'newsfeed/inc/AbstractStream.php';
require_once DOKU_PLUGIN . 'social/inc/OpenGraphData.php';

use dokuwiki\Extension\Plugin;
use FYKOS\dokuwiki\Extenstion\PluginSocial\OpenGraphData;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model\News;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model\Stream;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Renderer\AbstractRenderer;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Renderer\FykosRenderer;
use FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Renderer\VyfukRenderer;

class helper_plugin_newsfeed extends Plugin {

    public static array $fields = [
        'title',
        'authorName',
        'authorEmail',
        'newsDate',
        'image',
        'category',
        'linkHref',
        'linkTitle',
        'text',
    ];
    public helper_plugin_sqlite $sqlite;

    private OpenGraphData $openGraphData;

    private AbstractRenderer $renderer;

    const FORM_TARGET = 'plugin_news-feed';

    public function __construct() {

        $this->openGraphData = new OpenGraphData();

        $this->sqlite = $this->loadHelper('sqlite');
        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }

        if (!$this->sqlite->init('newsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
        }

        switch ($this->getConf('contest')) {
            default:
            case 'fykos':
                $this->renderer = new FykosRenderer($this);
                break;
            case 'vyfuk':
                $this->renderer = new VyfukRenderer($this);
                break;
        }
    }

    public function getRenderer(): AbstractRenderer {
        return $this->renderer;
    }

    public function getOpenGraphData(): OpenGraphData {
        return $this->openGraphData;
    }

    /**
     * @return Stream[]
     */
    public function getAllStreams(): array {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM stream');
        foreach ($this->sqlite->res2arr($res) as $row) {
            $stream = new Stream($this->sqlite);
            $stream->fill($row);
            $streams[] = $stream;
        }

        return $streams;
    }

    /**
     * @param $streamId integer
     * @return integer[]
     * @deprecated
     */
    private function allParentDependence(int $streamId): array {
        $streamIds = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $streamId);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIds[] = $row['child'];
        }
        return $streamIds;
    }

    /**
     * @param $streamId integer
     * @return integer[]
     * @deprecated
     */
    private function allChildDependence(int $streamId): array {
        $streamIds = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $streamId);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIds[] = $row['parent'];
        }
        return $streamIds;
    }

    /**
     * @param $streamId integer
     * @param array $arr
     * @return void
     * @deprecated
     */
    public function fullParentDependence(int $streamId, array &$arr): void {
        foreach ($this->allParentDependence($streamId) as $newStreamId) {
            if (!in_array($newStreamId, $arr)) {
                $arr[] = $newStreamId;
                $this->fullParentDependence($newStreamId, $arr);
            }
        }
    }

    /**
     * @param $streamId
     * @param array $arr
     * @return void
     * @deprecated
     */
    public function fullChildDependence(int $streamId, array &$arr): void {
        foreach ($this->allChildDependence($streamId) as $newStreamId) {
            if (!in_array($newStreamId, $arr)) {
                $arr[] = $newStreamId;
                $this->fullChildDependence($newStreamId, $arr);
            }
        }
    }

    /**
     * @return News[]
     */
    public function getAllNewsFeed(): array {
        $res = $this->sqlite->query('SELECT * FROM news');
        $news = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $feed = new News($this->sqlite, $row['news_id']);
            $feed->load();
            $news[] = $feed;
        }
        return $news;
    }
}

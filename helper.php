<?php

require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/AbstractModel.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/News.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/Priority.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/model/Stream.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/AbstractRenderer.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/VyfukRenderer.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/renderer/FykosRenderer.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/AbstractStream.php';

use PluginNewsFeed\Model\News;
use PluginNewsFeed\Model\Stream;

class helper_plugin_fksnewsfeed extends \DokuWiki_Plugin {

    public static $fields = [
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
    /**
     * @var helper_plugin_sqlite
     */
    public $sqlite;
    /**
     * @var helper_plugin_social
     */
    public $social;

    const FORM_TARGET = 'plugin_news-feed';

    public function __construct() {
        $this->social = $this->loadHelper('social');

        $this->sqlite = $this->loadHelper('sqlite');
        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }
        if (!$this->sqlite->init('fksnewsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
        }
    }

    /**
     * @return Stream[]
     */
    public function getAllStreams() {
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
     * @param $name
     * @return Stream
     */
    public function loadStream($name) {
        $stream = new Stream($this->sqlite);
        $stream->findByName($name);
        return $stream;
    }

    private function allParentDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['child'];
        }
        return $streamIDs;
    }

    private function allChildDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['parent'];
        }
        return $streamIDs;
    }

    public function fullParentDependence($streamID, &$arr) {
        foreach ($this->allParentDependence($streamID) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullParentDependence($newStreamID, $arr);
            }
        }
    }

    public function fullChildDependence($streamID, &$arr) {
        foreach ($this->allChildDependence($streamID) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullChildDependence($newStreamID, $arr);
            }
        }
    }

    /**
     * @return News[]
     */
    public function allNewsFeed() {
        $res = $this->sqlite->query('SELECT * FROM news');
        $news = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $feed = new News($this->sqlite, $row['news_id']);
            $feed->load();
            $news[] = $feed;
        };
        return $news;
    }
}

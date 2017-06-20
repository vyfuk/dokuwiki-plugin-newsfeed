<?php

require_once DOKU_PLUGIN . 'fksnewsfeed/inc/News.php';
require_once DOKU_PLUGIN . 'fksnewsfeed/inc/Priority.php';

class helper_plugin_fksnewsfeed extends DokuWiki_Plugin {

    public static $fields = [
        'title',
        'author-name',
        'author-email',
        'news-date',
        'image',
        'category',
        'link-href',
        'link-title',
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

    const SIMPLE_RENDER_PATTERN = '{{news-feed>id="@id@" even="@even@" editable="@editable@" stream="@stream@" page_id="@page_id@"}}';
    const db_table_feed = 'news';
    const db_table_dependence = 'dependence';
    const db_table_order = 'priority';
    const db_table_stream = 'stream';
    const db_view_dependence = 'v_dependence';

    const FORM_TARGET = 'plugin_news-feed';

    public function __construct() {
        $this->social = $this->loadHelper('social');

        $this->sqlite = $this->loadHelper('sqlite', false);

        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }
        if (!$this->sqlite->init('fksnewsfeed',
            DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
        }
    }

    public function streamToID($stream) {

        $res1 = $this->sqlite->query('SELECT stream_id FROM stream WHERE name=?', $stream);
        return (integer)$this->sqlite->res2single($res1);
    }

    /**
     * @param $stream
     * @return \PluginNewsFeed\News[]
     */
    public function loadStream($stream) {
        $streamID = $this->streamToID($stream);
        $res = $this->sqlite->query('SELECT * FROM priority o JOIN news n ON o.news_id=n.news_id WHERE stream_id=? ',
            $streamID);
        $ars = $this->sqlite->res2arr($res);
        $news = [];
        foreach ($ars as $ar) {
            $priority = new \PluginNewsFeed\Priority($ar);
            $priority->checkValidity();
            $news[] = new \PluginNewsFeed\News($ar, $priority);
        }
        usort($news,
            function (\PluginNewsFeed\News $a, \PluginNewsFeed\News $b) {
                if ($a->getPriority()->getPriorityValue() > $b->getPriority()->getPriorityValue()) {
                    return -1;
                } elseif ($a->getPriority()->getPriorityValue() < $b->getPriority()->getPriorityValue()) {
                    return 1;
                } else {
                    return strcmp($b->getNewsDate(), $a->getNewsDate());
                }
            });
        return $news;
    }

    public function findMaxNewsID() {
        $res = $this->sqlite->query('SELECT max(news_id) FROM news');
        return (int)$this->sqlite->res2single($res);
    }

    public function saveNews($data, $id = 0, $rewrite = false) {
        $values = [];
        foreach ($data as $key => $value) {
            $values[str_replace('-', '_', $key)] = $value;
        }
        if (!$rewrite) {
            $this->sqlite->query('INSERT INTO news 
                        (' . implode(',', array_keys($values)) . ')
            VALUES(?,?,?,?,?,?,?,?,?) ',
                $values);
            return $this->findMaxNewsID();
        } else {
            $data[] = $id;
            $this->sqlite->query('UPDATE news SET ' . implode(',',
                    array_map(function ($key) {
                        return $key . '=?';
                    },
                        array_keys($values))) . '           
            WHERE news_id=? ',
                $data);
            return $id;
        }
    }

    public function allStream() {
        $streams = [];
        $res = $this->sqlite->query('SELECT s.name FROM stream s');
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return $streams;
    }

    public function getToken($id, $pageID = '') {

        return (string)wl($pageID, null, true) . '?news-id=' . $id;
    }

    /**
     * @param $id integer
     * @param $toObject boolean
     * @return null|\PluginNewsFeed\News
     */
    public function loadSimpleNews($id, $toObject = true) {
        $res = $this->sqlite->query('SELECT * FROM news WHERE news_id=?', $id);
        foreach ($this->sqlite->res2arr($res) as $row) {
            return $toObject ? (new \PluginNewsFeed\News($row)) : $this->prepareRow($row);
        }
        return null;
    }

    private function prepareRow($row) {
        $values = [];
        foreach ($row as $key => $value) {
            $values[str_replace('_', '-', $key)] = $value;
        }
        return $values;
    }

    public function allParentDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence t WHERE t.parent=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['child'];
        }
        return $streamIDs;
    }

    public function allChildDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence t WHERE t.child=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['parent'];
        }
        return $streamIDs;
    }

    public function fullParentDependence($streamIDs, &$arr) {
        foreach ($this->allParentDependence($streamIDs) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullParentDependence($newStreamID, $arr);
            }
        }
    }

    public function fullChildDependence($streamIDs, &$arr) {
        foreach ($this->allChildDependence($streamIDs) as $newStreamID) {
            if (!in_array($newStreamID, $arr)) {
                $arr[] = $newStreamID;
                $this->fullChildDependence($newStreamID, $arr);
            }
        }
    }

    public function createStream($streamName) {
        $this->sqlite->query('INSERT INTO stream (name) VALUES(?);', $streamName);
        return $this->streamToID($streamName);
    }

    public function IDToStream($id) {
        $res1 = $this->sqlite->query('SELECT name FROM stream WHERE stream_id=?', $id);
        return (string)$this->sqlite->res2single($res1);
    }

    public function createDependence($parent, $child) {
        return (bool)$this->sqlite->query('INSERT INTO dependence (parent,child) VALUES(?,?);', $parent, $child);
    }

    /**
     * @return \PluginNewsFeed\News[]
     */
    public function allNewsFeed() {
        $res = $this->sqlite->query('SELECT * FROM news');
        $news = [];
        foreach ($this->sqlite->res2arr($res) as $row) {
            $news[] = new \PluginNewsFeed\News($row);
        };
        return $news;
    }

    public function printNews($newsID, $even, $stream, $pageID = '', $editable = true) {
        $n = str_replace(['@id@', '@even@', '@editable@', '@stream@', '@page_id@'],
            [
                $newsID,
                $even,
                $editable ? 'true' : 'false',
                $stream,
                $pageID,
            ],
            self::SIMPLE_RENDER_PATTERN);
        $info = [];
        return p_render('xhtml', p_get_instructions($n), $info);
    }
}

<?php

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
     * @var helper_plugin_fkshelper
     */
    public $FKS_helper;
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

        $this->FKS_helper = $this->loadHelper('fkshelper');

        $this->sqlite = $this->loadHelper('sqlite', false);

        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }
        if (!$this->sqlite->init('fksnewsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)) {
            msg($pluginName . ': Cannot initialize database.');
        }
    }

    public function streamToID($stream) {
        $res1 = $this->sqlite->query('SELECT stream_id FROM ' . self::db_table_stream . ' WHERE name=?', $stream);
        return (integer)$this->sqlite->res2single($res1);
    }

    public function loadStream($stream) {
        $streamID = $this->streamToID($stream);
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_order . ' o JOIN ' . self::db_table_feed . ' n ON o.news_id=n.news_id WHERE stream_id=? ', $streamID);
        $ars = $this->sqlite->res2arr($res);

        foreach ($ars as $key => $ar) {
            if ((time() < strtotime($ar['priority_from'])) || (time() > strtotime($ar['priority_to']))) {
                $ars[$key]['priority'] = 0;
            }
        }
        usort($ars, function ($a, $b) {
            if ($a['priority'] > $b['priority']) {
                return -1;
            } elseif ($a['priority'] < $b['priority']) {
                return 1;
            } else {
                return strcmp($b['news_date'], $a['news_date']);
            }
        });
        return (array)$ars;
    }

    public function findMaxNewsID() {
        $res = $this->sqlite->query('SELECT max(news_id) FROM ' . self::db_table_feed);
        return (int)$this->sqlite->res2single($res);
    }

    public function saveNews($data, $id = 0, $rewrite = false) {
        $values = [];
        foreach ($data as $key => $value) {
            $values[str_replace('-', '_', $key)] = $value;
        }
        if (!$rewrite) {
            $this->sqlite->query('INSERT INTO ' . self::db_table_feed . ' 
                        (' . implode(',', array_keys($values)) . ')
            VALUES(?,?,?,?,?,?,?,?,?) ', $values);
            return $this->findMaxNewsID();
        } else {
            $data[] = $id;
            $this->sqlite->query('UPDATE ' . self::db_table_feed . ' 
            SET ' . implode(',', array_map(function ($key) {
                    return $key . '=?';
                }, array_keys($values))) . '           
            WHERE news_id=? ', $data);
            return $id;
        }
    }

    public function allStream() {
        $streams = [];
        $res = $this->sqlite->query('SELECT s.name FROM ' . self::db_table_stream . ' s');
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return $streams;
    }

    public function getToken($id, $pageID = '') {
        return (string)wl($pageID, null, true) . '?fksnews_id=' . $id;
    }

    public function loadSimpleNews($id) {
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_feed . ' WHERE news_id=' . $id . ' ');
        foreach ($this->sqlite->res2arr($res) as $row) {
            return $this->prepareRow($row);
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

    public function allValues($field) {
        $values = [];
        $res = $this->sqlite->query('SELECT t.? FROM ' . self::db_table_feed . ' t GROUP BY t.?', $field, $field);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $values[] = $row[$field];
        }
        return $values;
    }

    public function allParentDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_dependence . ' t WHERE t.parent=?', $streamID);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['child'];
        }
        return $streamIDs;
    }

    public function allChildDependence($streamID) {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_dependence . ' t WHERE t.child=?', $streamID);
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

    public function saveIntoStream($streamID, $id) {
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_order . ' 
        WHERE news_id=? AND stream_id=?', $id, $streamID);
        if (count($this->sqlite->res2arr($res)) == 0) {
            $this->sqlite->query('INSERT INTO ' . self::db_table_order . ' (news_id,stream_id,priority) 
            VALUES(?,?,?)', $id, $streamID, 0);
        };
        return (int)1;
    }

    public function createStream($streamName) {
        $this->sqlite->query('INSERT INTO ' . self::db_table_stream . ' (name) VALUES(?);', $streamName);
        return $this->streamToID($streamName);
    }

    public function IDToStream($id) {
        $res1 = $this->sqlite->query('SELECT name FROM ' . self::db_table_stream . ' WHERE stream_id=?', $id);
        return (string)$this->sqlite->res2single($res1);
    }

    public function createDependence($parent, $child) {
        return (bool)$this->sqlite->query('INSERT INTO ' . self::db_table_dependence . ' (parent,child) VALUES(?,?);', $parent, $child);
    }

    public function savePriority($newsID, $streamID, $priority, $from, $to) {
        return $this->sqlite->query('UPDATE ' . self::db_table_order . ' 
        SET priority=?,priority_from=?,priority_to=? 
        WHERE stream_id=? AND news_id =?', $priority, $from, $to, $streamID, $newsID);
    }

    public function findPriority($newsID, $streamID) {
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_order . ' WHERE stream_id=? AND news_id =?', $streamID, $newsID);
        return $this->sqlite->res2arr($res);
    }

    public function allNewsFeed() {
        $res = $this->sqlite->query('SELECT * FROM ' . self::db_table_feed . ' ');

        return $this->sqlite->res2arr($res);
    }

    public function deleteOrder($newsID, $streamID) {
        $res = $this->sqlite->query('DELETE FROM ' . self::db_table_order . ' WHERE stream_id=? AND news_id =?', $streamID, $newsID);
        return $this->sqlite->res2arr($res);
    }

    public function getCacheFile($id) {
        return 'news-feed_news_' . $id;
    }

    public function printNews($newsID, $even, $stream, $pageID = '', $editable = true) {
        $n = str_replace(['@id@', '@even@', '@editable@', '@stream@', '@page_id@'], [
            $newsID,
            $even,
            $editable ? 'true' : 'false',
            $stream,
            $pageID
        ], self::SIMPLE_RENDER_PATTERN);
        $info = [];
        return p_render('xhtml', p_get_instructions($n), $info);
    }

}

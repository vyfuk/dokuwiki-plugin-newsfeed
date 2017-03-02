<?php

class helper_plugin_fksnewsfeed extends DokuWiki_Plugin {

    public $Fields = ['name', 'email', 'author', 'newsdate', 'image', 'category', 'text'];
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

    const SIMPLE_RENDER_PATERN = '{{fksnewsfeed>id="@id@"; even="@even@"; editable="@editable@";stream="@stream@";pageID="@page_id@"}}';
    const db_table_feed = "fks_newsfeed_news";
    const db_table_dependence = "fks_newsfeed_dependence";
    const db_table_order = "fks_newsfeed_order";
    const db_table_stream = "fks_newsfeed_stream";
    const db_view_dependence = "v_dependence";

    public function __construct() {
        $this->social = $this->loadHelper('social');

        $this->FKS_helper = $this->loadHelper('fkshelper');

        $this->sqlite = $this->loadHelper('sqlite', false);

        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
            return;
        }
        if (!$this->sqlite->init('fksnewsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)) {
            msg($pluginName . ': Cannot initialize database.');
            return;
        }
    }

    public function streamToID($stream) {
        $sql1 = 'SELECT stream_id FROM ' . self::db_table_stream . ' WHERE name=?';
        $res1 = $this->sqlite->query($sql1, $stream);
        $stream_id = $this->sqlite->res2single($res1);
        return (integer)$stream_id;
    }


    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $stream
     * @return array
     * load file with configuration
     * and load old configuration file
     */
    public function loadStream($stream) {
        $stream_id = $this->streamToID($stream);
        $sql = 'SELECT * FROM ' . self::db_table_order . ' o JOIN ' . self::db_table_feed . ' n ON o.news_id=n.news_id WHERE stream_id=? ';
        $res = $this->sqlite->query($sql, $stream_id);
        $ars = $this->sqlite->res2arr($res);

        foreach ($ars as $key => $ar) {
            if ((time() < strtotime($ar['priority_from'])) || (time() > strtotime($ar['priority_to']))) {
                $ars[$key]['priority'] = 0;
            } else {

            }
        }
        usort($ars, function ($a, $b) {
            if ($a['priority'] > $b['priority']) {
                return -1;
            } elseif ($a['priority'] < $b['priority']) {
                return 1;
            } else {
                return strcmp($b['newsdate'], $a['newsdate']);
            }
        });

        return (array)$ars;
    }

    /**
     * Find no news
     * @author Michal Červeňák <miso@fykos.cz>
     * @return int
     */
    public function findMaxNewsID() {
        $sql2 = 'SELECT max(news_id) FROM ' . self::db_table_feed;
        $res = $this->sqlite->query($sql2);
        $imax = $this->sqlite->res2single($res);

        return (int)$imax;
    }


    /**
     * save a new news or rewrite old
     * @author Michal Červeňák <miso@fykos.cz>
     * @return bool is write ok
     * @param array $data params to save
     * @param integer $id path to news
     * @param bool $rewrite rewrite?
     *
     */
    public function saveNews($data, $id = 0, $rewrite = false) {
        if (!$rewrite) {
            $sql = 'INSERT INTO ' . self::db_table_feed . ' (name, author, email,newsdate,text,image,category) VALUES(?,?,?,?,?,?,?) ;';
            $this->sqlite->query($sql, $data['name'], $data['author'], $data['email'], $data['newsdate'], $data['text'], $data['image'], $data['category']);
            return $this->findMaxNewsID();
        } else {
            $sql = 'UPDATE ' . self::db_table_feed . ' SET name=?, author=?, email=?, newsdate=?, text=?, image=?,category=? where news_id=? ';
            $this->sqlite->query($sql, $data['name'], $data['author'], $data['email'], $data['newsdate'], $data['text'], $data['image'], $data['category'], $id);
            return $id;
        }
    }

    /**
     * short name of news and add dots
     *
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name text to short
     * @param int $l length of output
     * @return string shorted text
     *
     *
     */
    public static function shortName($name = "", $l = 25) {
        if (strlen($name) > $l) {
            $name = mb_substr($name, 0, $l - 3) . '...';
        }
        return (string)$name;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array all stream from dir
     */
    public function allStream() {
        $streams = [];
        $sql = 'SELECT s.name FROM ' . self::db_table_stream . ' s';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return $streams;
    }


    public function getToken($id, $page_id = "") {
        return (string)wl($page_id, null, true) . '?fksnews_id=' . $id;
    }

    /**
     * load news @i@ and return text
     * @author     Michal Červeňák <miso@fykos.cz>
     * @param int $id
     * @return array
     */
    public function loadSimpleNews($id) {
        $sql = 'SELECT * FROM ' . self::db_table_feed . ' WHERE news_id=' . $id . ' ';
        $res = $this->sqlite->query($sql);

        foreach ($this->sqlite->res2arr($res) as $row) {
            return $row;
        }
        return null;
    }

    /**
     *
     * @param string $field name of field
     * @return array
     */
    public function allValues($field) {
        $values = [];
        $sql = 'SELECT t.? FROM ' . self::db_table_feed . ' t GROUP BY t.?';
        $res = $this->sqlite->query($sql, $field, $field);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $values[] = $row[$field];
        }
        return $values;
    }

    public function allParentDependence($stream_id) {

        $stream_ids = [];
        $sql = 'SELECT * FROM ' . self::db_table_dependence . ' t WHERE t.parent=?';
        $res = $this->sqlite->query($sql, $stream_id);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['child'];
        }
        return $stream_ids;
    }

    /**
     *
     * @param int $stream_id
     * @return array of stream ID
     */
    public function allChildDependence($stream_id) {

        $stream_ids = [];
        $sql = 'SELECT * FROM ' . self::db_table_dependence . ' t WHERE t.child=?';
        $res = $this->sqlite->query($sql, $stream_id);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['parent'];
        }
        return $stream_ids;
    }

    /**
     * @param $stream_id
     * @param $arr
     * @deprecated
     */
    public function create_dependence($stream_id, &$arr) {
        $this->fullParentDependence($stream_id, $arr);
    }

    public function fullParentDependence($stream_id, &$arr) {
        foreach ($this->allParentDependence($stream_id) as $new_stream_id) {
            if (!in_array($new_stream_id, $arr)) {
                $arr[] = $new_stream_id;
                $this->fullParentDependence($new_stream_id, $arr);
            }
        }
    }

    public function fullChildDependence($stream_id, &$arr) {

        foreach ($this->allChildDependence($stream_id) as $new_stream_id) {
            if (!in_array($new_stream_id, $arr)) {
                $arr[] = $new_stream_id;
                $this->fullChildDependence($new_stream_id, $arr);
            }
        }
    }

    public function saveIntoStream($stream_id, $id) {
        $sql2 = 'SELECT * FROM ' . self::db_table_order . ' WHERE news_id=? AND stream_id=?';
        $res = $this->sqlite->query($sql2, $id, $stream_id);
        if (count($this->sqlite->res2arr($res)) == 0) {
            $sql3 = 'INSERT INTO ' . self::db_table_order . ' (news_id,stream_id,priority) VALUES(?,?,?)';
            $this->sqlite->query($sql3, $id, $stream_id, 0);
        };
        return (int)1;
    }

    public function createStream($stream_name) {
        $sql1 = 'INSERT INTO ' . self::db_table_stream . ' (name) VALUES(?);';
        $this->sqlite->query($sql1, $stream_name);
        $stream_id = $this->streamToID($stream_name);
        return $stream_id;
    }

    /**
     * return name of stream.
     * @author Michal Cervenak <miso@fykos.cz>
     *
     * @param int $id referent id of stream
     * @return string
     */
    public function IDToStream($id) {
        $sql1 = 'SELECT name FROM ' . self::db_table_stream . ' WHERE stream_id=?';
        $res1 = $this->sqlite->query($sql1, $id);
        $stream_name = $this->sqlite->res2single($res1);

        return (string)$stream_name;
    }

    /**
     * Create dependence betwen parent and child stream
     * @author Michal Cervenak <miso@fykos.cz>
     *
     * @param int $parent id of parent stream
     * @param int $child id of child stream
     * @return boolean
     */
    public function createDependence($parent, $child) {
        $sql1 = 'INSERT INTO ' . self::db_table_dependence . ' (parent,child) VALUES(?,?);';
        $r = $this->sqlite->query($sql1, $parent, $child);
        return (bool)$r;
    }

    public function savePriority($news_id, $stream_id, $p, $from, $to) {
        $sql = 'UPDATE ' . self::db_table_order . ' SET priority=?,priority_from=?,priority_to=? WHERE stream_id=? AND news_id =?';
        $this->sqlite->query($sql, $p, $from, $to, $stream_id, $news_id);
        return 1;
    }

    public function findPriority($news_id, $stream_id) {
        $sql = 'SELECT * FROM ' . self::db_table_order . ' WHERE stream_id=? AND news_id =?';
        $res = $this->sqlite->query($sql, $stream_id, $news_id);

        return $this->sqlite->res2arr($res);
    }

    public function allNewsFeed() {
        $sql = 'SELECT * FROM ' . self::db_table_feed . ' ';
        $res = $this->sqlite->query($sql);

        return $this->sqlite->res2arr($res);
    }

    public function deleteOrder($news_id, $stream_id) {
        $sql = 'DELETE FROM ' . self::db_table_order . ' WHERE stream_id=? AND news_id =?';
        $res = $this->sqlite->query($sql, $stream_id, $news_id);
        return $this->sqlite->res2arr($res);
    }

    public function getCacheFile($id) {
        return 'FKS_newsfeed_news_' . $id;
    }

    public function printNews($id, $e, $stream, $page_id = "", $editable = true) {
        $n = str_replace(['@id@', '@even@', '@editable@', '@stream@', '@page_id@'], [
            $id,
            $e,
            $editable ? 'true' : 'false',
            $stream,
            $page_id
        ], self::SIMPLE_RENDER_PATERN);
        $info = [];

        return p_render("xhtml", p_get_instructions($n), $info);
    }

}

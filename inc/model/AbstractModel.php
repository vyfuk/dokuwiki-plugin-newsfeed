<?php

namespace PluginNewsFeed\Model;


abstract class AbstractModel {
    /**
     * @var \helper_plugin_sqlite
     */
    protected $sqlite;

    public function __construct(\helper_plugin_sqlite &$sqlite) {

        $this->sqlite = $sqlite;
        if (!$this->sqlite->getAdapter()->getDb()) {
          //  $this->sqlite->getAdapter()->opendb(false);
        }
    }

    abstract public function load();

    abstract public function create();

    abstract public function update();

    public function findMaxNewsId() {
        $res = $this->sqlite->query('SELECT max(news_id) FROM news');
        return (int)$this->sqlite->res2single($res);
    }

}

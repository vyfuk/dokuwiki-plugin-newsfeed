<?php

namespace FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model;

use helper_plugin_sqlite;

abstract class AbstractModel {

    protected helper_plugin_sqlite $sqlite;

    public function __construct(helper_plugin_sqlite $sqlite) {

        $this->sqlite = $sqlite;
        if (!$this->sqlite->getAdapter()->getDb()) {
            //  $this->sqlite->getAdapter()->opendb(false);
        }
    }

    abstract public function load(): void;

    abstract public function create();

    abstract public function update();

    public function findMaxNewsId(): int {
        $res = $this->sqlite->query('SELECT max(news_id) FROM news');
        return (int)$this->sqlite->res2single($res);
    }

}

<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;

use helper_plugin_sqlite;

/**
 * Class AbstractModel
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractModel {

    protected $sqlite;

    public function __construct(helper_plugin_sqlite $sqlite) {
        $this->sqlite = $sqlite;
    }

    abstract public static function createFromArray(\helper_plugin_sqlite $helperPluginSqlite, array $data);
}

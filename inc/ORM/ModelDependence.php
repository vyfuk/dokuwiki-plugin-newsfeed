<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;
use helper_plugin_sqlite;

/**
 * Class Dependence
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelDependence extends AbstractModel {

    public static function createFromArray(helper_plugin_sqlite $helperPluginSqlite, array $data) {
        return new self($helperPluginSqlite);
    }
}

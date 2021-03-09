<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use helper_plugin_sqlite;

/**
 * Class ServiceNews
 * @author Michal Červeňák <miso@fykos.cz>
 * @method ModelNews getById(int $id)
 */
class ServiceNews extends AbstractService {
    public function __construct(helper_plugin_sqlite $sqlite) {
        parent::__construct($sqlite, 'news', ModelNews::class);
    }
}

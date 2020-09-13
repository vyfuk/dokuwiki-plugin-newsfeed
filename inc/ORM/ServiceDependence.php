<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelDependence;
use helper_plugin_sqlite;

/**
 * Class ServiceNews
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceDependence extends AbstractService {
    public function __construct(helper_plugin_sqlite $sqlite) {
        parent::__construct($sqlite, 'dependence', ModelDependence::class);
    }
}

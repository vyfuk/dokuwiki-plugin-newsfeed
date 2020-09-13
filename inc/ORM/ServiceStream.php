<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelStream;
use helper_plugin_sqlite;

/**
 * Class ServiceNews
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceStream extends AbstractService {
    public function __construct(helper_plugin_sqlite $sqlite) {
        parent::__construct($sqlite, 'stream', ModelStream::class);
    }

    public function findByName(string $name): ?ModelStream {
        $res = $this->sqlite->query('SELECT * FROM ? WHERE name=?', $this->table, $name);
        return ($this->modelClassName)::createFromArray($this->sqlite, $res->fetch());
    }
}

<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\AbstractModel;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelPriority;
use helper_plugin_sqlite;

/**
 * Class ServiceNews
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServicePriority extends AbstractService {
    public function __construct(helper_plugin_sqlite $sqlite) {
        parent::__construct($sqlite, 'priority', ModelPriority::class);
    }

    public function findByNewsAndStream(int $newsId, int $streamId): ModelPriority {
        $res = $this->sqlite->query('SELECT * FROM ? WHERE stream_id=? AND news_id =?', $this->table, $streamId, $newsId);
        return ($this->modelClassName)::createFromArray($this->sqlite, $res->fetch());
    }

    public function update(AbstractModel $model, array $data) {

        if ((time() < strtotime($data['priority_from'])) || (time() > strtotime($data['priority_to']))) {
            $data['priority_value'] = 0;
        }

        parent::update($model, $data);
    }
}

<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\AbstractModel;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelPriority;
use helper_plugin_sqlite;

/**
 * Class ServiceNews
 * @author Michal Červeňák <miso@fykos.cz>
 * @property ModelPriority $modelClassName
 */
class ServicePriority extends AbstractService {

    public function __construct(helper_plugin_sqlite $sqlite) {
        parent::__construct($sqlite, 'priority', ModelPriority::class);
    }

    public function findByNewsAndStream(int $newsId, int $streamId): ?ModelPriority {
        $res = $this->sqlite->query('SELECT * FROM ? WHERE stream_id=? AND news_id =?', $this->table, $streamId, $newsId)->fetch();
        return $res ? ($this->modelClassName)::createFromArray($this->sqlite, $res) : null;
    }

    public function update(AbstractModel $model, array $data): void {
        if ((time() < strtotime($data['priority_from'])) || (time() > strtotime($data['priority_to']))) {
            $data['priority_value'] = 0;
        }
        parent::update($model, $data);
    }

    public function store(int $newsId, int $streamId): void {
        $model = $this->findByNewsAndStream($newsId, $streamId);
        if ($model) {
            $this->update($model, ['news_id' => $newsId, 'stream_id' => $streamId]);
        } else {
            $this->create(['news_id' => $newsId, 'stream_id' => $streamId, 'priority' => 0]);
        }
    }
}

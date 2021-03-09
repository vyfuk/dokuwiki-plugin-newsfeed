<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;

use helper_plugin_sqlite;

/**
 * Class Priority
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelPriority extends AbstractModel {

    public $priorityId;

    public $priorityFrom;

    public $priorityTo;

    private $priorityValue;

    public $newsId;

    public $streamId;

    public function getPriorityValue(): int {
        if ((time() < strtotime($this->priorityFrom)) || (time() > strtotime($this->priorityTo))) {
            return 0;
        }
        return $this->priorityValue;
    }

    public function delete() {
        $res = $this->sqlite->query('DELETE FROM priority WHERE stream_id=? AND news_id =?', $this->streamId, $this->newsId);
        return $this->sqlite->res2arr($res);
    }

    public static function createFromArray(helper_plugin_sqlite $helperPluginSqlite, array $data): self {
        $model = new self($helperPluginSqlite);
        $model->priorityId = $data['priority_id'];
        $model->newsId = $data['news_id'];
        $model->streamId = $data['stream_id'];
        $model->priorityValue = $data['priority'] ?? 0;
        $model->priorityFrom = $data['priority_from'];
        $model->priorityTo = $data['priority_to'];

        return $model;
    }
}

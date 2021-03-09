<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServiceNews;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServiceStream;
use helper_plugin_sqlite;

/**
 * Class Stream
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelStream extends AbstractModel {

    public $streamId;

    public $name;

    /**
     * @return ModelNews[]
     */
    public function getNews(): array {
        $res = $this->sqlite->query('SELECT * FROM priority o JOIN news n ON o.news_id=n.news_id WHERE stream_id=? ',
            $this->streamId);
        $ars = $this->sqlite->res2arr($res);
        $news = [];
        $service = new ServiceNews($this->sqlite);
        foreach ($ars as $ar) {
            $priority = ModelPriority::createFromArray($this->sqlite, $ar);

            $feed = $service->getById($ar['news_id']);
            $feed->setPriority($priority);
            $news[] = $feed;
        }
        return $this->sortNews($news);
    }

    private function sortNews(array $news): array {
        usort($news,
            function (ModelNews $a, ModelNews $b) {
                if ($a->getPriority()->getPriorityValue() > $b->getPriority()->getPriorityValue()) {
                    return -1;
                } elseif ($a->getPriority()->getPriorityValue() < $b->getPriority()->getPriorityValue()) {
                    return 1;
                } else {
                    return strcmp($b->newsDate, $a->newsDate);
                }
            });
        return $news;
    }

    /**
     * @return ModelStream[]
     */
    public function getAllParentDependence(): array {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $this->streamId);
        $service = new ServiceStream($this->sqlite);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $service->getById($row['child']);
        }
        return $streams;
    }

    /**
     * @return ModelStream[]
     */
    public function getAllChildDependence(): array {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $this->streamId);
        $service = new ServiceStream($this->sqlite);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $service->getById($row['parent']);
        }
        return $streams;
    }

    public static function createFromArray(helper_plugin_sqlite $helperPluginSqlite, array $data) {
        $model = new self($helperPluginSqlite);
        $model->name = $data['name'];
        $model->streamId = $data['stream_id'];
        return $model;
    }
}

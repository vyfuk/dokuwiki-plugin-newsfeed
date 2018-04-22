<?php

namespace PluginNewsFeed\Model;


class Stream extends AbstractModel {
    /**
     * @var integer
     */
    private $streamId;

    /**
     * @param int $streamId
     */
    public function setStreamId($streamId) {
        $this->streamId = $streamId;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @var string
     */
    private $name;

    public function __construct(\helper_plugin_sqlite &$sqlite, $streamId = null) {
        parent::__construct($sqlite);
        $this->streamId = $streamId;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return integer
     */
    public function getStreamID() {
        return $this->streamId;
    }

    /**
     * @return News[]
     */
    public function getNews() {
        ;
        $res = $this->sqlite->query('SELECT * FROM priority o JOIN news n ON o.news_id=n.news_id WHERE stream_id=? ',
            $this->streamId);
        $ars = $this->sqlite->res2arr($res);
        $news = [];
        foreach ($ars as $ar) {
            $priority = new Priority($this->sqlite);
            $priority->fill($ar);

            $feed = new News($this->sqlite, $ar['news_id']);
            $feed->load();
            $feed->setPriority($priority);
            $news[] = $feed;
        }
        return $this->sortNews($news);
    }

    private function sortNews($news) {
        usort($news,
            function (News $a, News $b) {
                if ($a->getPriority()->getPriorityValue() > $b->getPriority()->getPriorityValue()) {
                    return -1;
                } elseif ($a->getPriority()->getPriorityValue() < $b->getPriority()->getPriorityValue()) {
                    return 1;
                } else {
                    return strcmp($b->getNewsDate(), $a->getNewsDate());
                }
            });
        return $news;
    }

    public function update() {
        msg('not implement', -1);
        return;
    }

    public function fill($data) {
        $this->name = $data['name'];
        $this->streamId = $data['stream_id'];
    }

    public function findByName($name) {
        $res = $this->sqlite->query('SELECT * FROM stream WHERE name=?', $name);
        $this->fill($this->sqlite->res2row($res));
    }

    public function load() {
        $res = $this->sqlite->query('SELECT name FROM stream WHERE stream_id=?', $this->streamId);
        $this->name = $this->sqlite->res2single($res);
    }

    public function create() {
        $this->sqlite->query('INSERT INTO stream (name) VALUES(?)', $this->name);
        $this->findByName($this->name);
        return $this->name;
    }

    /**
     * @return integer[]
     */
    public function getAllParentDependence() {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $this->streamId);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['child'];
        }
        return $streamIDs;
    }

    /**
     * @return integer[]
     */
    public function getAllChildDependence() {
        $streamIDs = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $this->streamId);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIDs[] = $row['parent'];
        }
        return $streamIDs;
    }
}

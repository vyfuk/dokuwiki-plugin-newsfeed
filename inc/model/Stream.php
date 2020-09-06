<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;

class Stream extends AbstractModel {

    private ?int $streamId;

    private ?string $name;

    public function __construct(\helper_plugin_sqlite $sqlite, ?int $streamId = null) {
        parent::__construct($sqlite);
        $this->streamId = $streamId;
    }

    public function setStreamId(int $streamId): void {
        $this->streamId = $streamId;
    }

    public function setName(?string $name): void {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getStreamId(): ?int {
        return $this->streamId;
    }

    /**
     * @return News[]
     */
    public function getNews(): array {
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

    private function sortNews(array $news): array {
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
    }

    public function fill($data) {
        $this->name = $data['name'];
        $this->streamId = $data['stream_id'];
    }

    public function findByName($name) {
        $res = $this->sqlite->query('SELECT * FROM stream WHERE name=?', $name);
        $this->fill($this->sqlite->res2row($res));
    }

    public function load(): void {
        $res = $this->sqlite->query('SELECT name FROM stream WHERE stream_id=?', $this->streamId);
        $this->name = $this->sqlite->res2single($res);
    }

    public function create() {
        $this->sqlite->query('INSERT INTO stream (name) VALUES(?)', $this->name);
        $this->findByName($this->name);
        return $this->name;
    }

    /**
     * @return Stream[]
     */
    public function getAllParentDependence() {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $this->getStreamId());
        foreach ($this->sqlite->res2arr($res) as $row) {
            $stream = new Stream($this->sqlite);
            $stream->setStreamId($row['child']);
            $streams[] = $stream;
        }
        return $streams;
    }

    /**
     * @return Stream[]
     */
    public function getAllChildDependence() {
        $streams = [];
        $res = $this->sqlite->query('SELECT * FROM dependence  WHERE child=?', $this->getStreamId());
        foreach ($this->sqlite->res2arr($res) as $row) {
            $stream = new Stream($this->sqlite);
            $stream->setStreamId($row['parent']);
            $streams[] = $stream;

        }
        return $streams;
    }
}

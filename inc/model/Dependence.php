<?php

namespace FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model;


class Dependence extends AbstractModel {
    /**
     * @var integer
     */
    private $dependenceId;

    /**
     * @var Stream
     */
    private $parentStream;
    /**
     * @var Stream
     */
    private $childStream;

    public function load(): void {
        $res = $this->sqlite->query('SELECT * FROM dependence where dependence_id=?', $this->getDependenceId());
        $row = $this->sqlite->res2single($res);
        if ($row) {
            $parent = new Stream($this->sqlite);
            $parent->setStreamId($row->parent);
            $child = new Stream($this->sqlite);
            $child->setStreamId($row->child);
        }
    }

    public function create(): bool {
        return (bool)$this->sqlite->query('INSERT INTO dependence (parent,child) VALUES(?,?);', $this->parentStream->getStreamId(), $this->childStream->getStreamId());
    }

    public function update() {

    }

    /**
     * @return int
     */
    public function getDependenceId() {
        return $this->dependenceId;
    }

    /**
     * @param int $dependenceId
     */
    public function setDependenceId($dependenceId) {
        $this->dependenceId = $dependenceId;
    }

    /**
     * @return Stream
     */
    public function getParentStream() {
        return $this->parentStream;
    }

    /**
     * @param Stream $parentStream
     */
    public function setParentStream(Stream $parentStream) {
        $this->parentStream = $parentStream;
    }

    /**
     * @return Stream
     */
    public function getChildStream() {
        return $this->childStream;
    }

    /**
     * @param Stream $childStream
     */
    public function setChildStream(Stream $childStream) {
        $this->childStream = $childStream;
    }

    /**
     * @return int
     */
    public function dependenceExist() {
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent= ? AND child =?;', $this->parentStream->getStreamId(), $this->childStream->getStreamId());
        return $this->sqlite->res2count($res);
    }

}

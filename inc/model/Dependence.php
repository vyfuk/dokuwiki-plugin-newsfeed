<?php

namespace PluginNewsFeed\Model;


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

    public function load() {

    }

    public function create() {
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

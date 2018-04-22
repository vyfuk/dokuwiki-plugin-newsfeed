<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 20.6.2017
 * Time: 15:54
 */

namespace PluginNewsFeed\Model;


class Priority extends AbstractModel {
    /**
     * @var integer
     */
    private $priorityId;

    /**
     * @var string;
     */
    private $priorityFrom;
    /**
     * @var string
     */
    private $priorityTo;
    /**
     * @var integer
     */
    private $priorityValue;
    /**
     * @var integer
     */
    private $newsId;
    /**
     * @var integer
     */
    private $streamId;

    /**
     * @return integer
     */
    public function getId() {
        return $this->priorityId;
    }

    /**
     * @return string
     */
    public function getPriorityFrom() {
        return $this->priorityFrom;
    }

    /**
     * @return string
     */
    public function getPriorityTo() {
        return $this->priorityTo;
    }

    /**
     * @return integer
     */
    public function getPriorityValue() {
        return $this->priorityValue;
    }

    /**
     * @param int $priorityId
     */
    public function setPriorityId($priorityId) {
        $this->priorityId = $priorityId;
    }

    /**
     * @param string $priorityFrom
     */
    public function setPriorityFrom($priorityFrom) {
        $this->priorityFrom = $priorityFrom;
    }

    /**
     * @param string $priorityTo
     */
    public function setPriorityTo($priorityTo) {
        $this->priorityTo = $priorityTo;
    }

    /**
     * @param int $priorityValue
     */
    public function setPriorityValue($priorityValue) {
        $this->priorityValue = $priorityValue;
    }

    /**
     * @param int $newsId
     */
    public function setNewsId($newsId) {
        $this->newsId = $newsId;
    }

    /**
     * @param int $streamId
     */
    public function setStreamId($streamId) {
        $this->streamId = $streamId;
    }

    public function checkValidity() {
        if ((time() < strtotime($this->priorityFrom)) || (time() > strtotime($this->priorityTo))) {
            $this->priorityValue = 0;
        }
    }

    public function update() {
        return $this->sqlite->query('UPDATE priority SET priority=?,priority_from=?,priority_to=? WHERE stream_id=? AND news_id =?',
            $this->priorityValue,
            $this->priorityFrom,
            $this->priorityTo,
            $this->streamId,
            $this->newsId);
    }

    public function create() {
        $res = $this->sqlite->query('SELECT * FROM priority WHERE news_id=? AND stream_id=?',
            $this->newsId,
            $this->streamId);
        if (count($this->sqlite->res2arr($res)) == 0) {
            $this->sqlite->query('INSERT INTO priority (news_id,stream_id,priority) VALUES(?,?,?)',
                $this->newsId,
                $this->streamId,
                0);
        };
        return (int)1;
    }

    public function delete() {
        $res = $this->sqlite->query('DELETE FROM priority WHERE stream_id=? AND news_id =?', $this->streamId, $this->newsId);
        return $this->sqlite->res2arr($res);
    }

    public function load() {
        $res = $this->sqlite->query('SELECT * FROM priority WHERE stream_id=? AND news_id =?', $this->streamId, $this->newsId);
        return $this->fill($this->sqlite->res2row($res));
    }

    public function fill($data) {
        $this->priorityFrom = $data['priority_from'];
        $this->priorityId = $data['priority_id'];
        $this->priorityTo = $data['priority_to'];
        $this->priorityValue = $data['priority'];
        $this->checkValidity();
        return true;
    }

    public function __construct(\helper_plugin_sqlite &$sqlite, $params = [], $newsId = null, $streamId = null) {
        parent::__construct($sqlite);
        $this->fill($params);
        $this->newsId = $newsId;
        $this->streamId = $streamId;
    }
}

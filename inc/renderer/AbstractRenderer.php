<?php

namespace PluginNewsFeed\Renderer;

use \PluginNewsFeed\Model\News;

abstract class AbstractRenderer {
    /**
     * @var \helper_plugin_fksnewsfeed
     */
    protected $helper;

    public function __construct(\helper_plugin_fksnewsfeed $helper) {
        $this->helper = $helper;
    }

    abstract public function renderContent(News $data, $params);

    abstract public function renderEditFields($params);

    abstract public function render($innerHtml, $formHtml, News $news);

    abstract protected function getModalHeader();

    /**
     * @param $id
     * @param $streamName
     * @param $params
     * @return string
     */
    abstract protected function getPriorityField($id, $streamName, $params);

    abstract protected function btnEditNews($id, $stream);

    /**
     * @param $news News
     * @return string
     */
    abstract protected function getShareFields(News $news);

    /**
     * @param $news News
     * @return null|string
     */
    abstract protected function getText($news);

    /**
     * @param $news News
     * @return string
     */
    abstract protected function getSignature($news);

    /**
     * @param $news News
     * @return string
     */
    abstract protected function getHeader($news);

    /**
     * @param $news News
     * @return string
     */
    abstract protected function getLink($news);
}

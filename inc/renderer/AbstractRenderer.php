<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Renderer;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use helper_plugin_newsfeed;

/**
 * Class AbstractRenderer
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractRenderer {

    protected $helper;

    public function __construct(helper_plugin_newsfeed $helper) {
        $this->helper = $helper;
    }

    abstract public function renderContent(ModelNews $data, array $params): string;

    abstract public function renderEditFields(array $params): string;

    abstract public function render(string $innerHtml, string $formHtml, ModelNews $news): string;
}

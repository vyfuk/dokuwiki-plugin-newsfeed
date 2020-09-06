<?php

use dokuwiki\Extension\AdminPlugin;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\Stream;

/**
 * Class admin_plugin_newsfeed_dependence
 * @author Michal Červeňák <miso@fykos.cz>
 */
class admin_plugin_newsfeed_dependence extends AdminPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function getMenuSort(): int {
        return 291;
    }

    public function forAdminOnly(): bool {
        return false;
    }

    public function getMenuText($lang): string {
        return $this->getLang('dependence_menu');
    }

    public function html(): void {
        echo '<h1>' . $this->getLang('dependence_menu') . '</h1>';

        $streams = $this->helper->getAllStreams();

        echo '<h2>' . $this->getLang('dep_list') . ':</h2>';

        foreach ($streams as $stream) {
            echo '<h3>' . $this->getLang('stream') . ': <span class="badge badge-primary">' .
                $stream->getName() . '</span></h3>';

            $this->renderParentDependence($stream);
            $this->renderChildDependence($stream);
            echo '<hr class="clearfix">';
        }
    }

    private function renderChildDependence(Stream $stream) {
        $childDependence = $stream->getAllChildDependence();
        echo '<h4>' . $this->getLang('dep_list_child') . '</h4>';
        if (!empty($childDependence)) {
            echo '<ul>';
            foreach ($childDependence as $dependenceStream) {
                $dependenceStream->load();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }

        $fullChildDependence = [];
        $this->helper->fullChildDependence($stream->getStreamId(), $fullChildDependence);
        echo '<h4>' . $this->getLang('dep_list_child_full') . '</h4>';
        if (!empty($fullChildDependence)) {
            echo '<ul>';
            foreach ($fullChildDependence as $dependence) {
                $dependenceStream = new Stream($this->helper->sqlite);
                $dependenceStream->setStreamId($dependence);
                $dependenceStream->load();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }
    }

    private function renderParentDependence(Stream $stream) {
        echo '<h4>' . $this->getLang('dep_list_parent') . '</h4>';

        $parentDependence = $stream->getAllParentDependence();
        if (!empty($parentDependence)) {
            echo '<ul>';
            foreach ($parentDependence as $dependenceStream) {

                $dependenceStream->load();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }

        $fullParentDependence = [];
        $this->helper->fullParentDependence($stream->getStreamId(), $fullParentDependence);
        echo '<h4>' . $this->getLang('dep_list_parent_full') . '</h4>';
        if (!empty($fullParentDependence)) {
            echo '<ul>';
            foreach ($fullParentDependence as $dependence) {
                $dependenceStream = new Stream($this->helper->sqlite);
                $dependenceStream->setStreamId($dependence);
                $dependenceStream->load();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }
    }
}

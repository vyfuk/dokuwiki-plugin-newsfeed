<?php

use PluginNewsFeed\Model\Dependence;
use \PluginNewsFeed\Model\Stream;
use \dokuwiki\Form\Form;

class admin_plugin_fksnewsfeed_dependence extends DokuWiki_Admin_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 291;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText($lang) {
        $menuText = $this->getLang('dependence_menu');
        return $menuText;
    }

    public function handle() {
        global $INPUT;
        $dep = $INPUT->param('dep');
        if ($dep['child'] == '' || $dep['parent'] == '') {
            return;
        }
        $childStream = new Stream($this->helper->sqlite);
        $childStream->findByName($dep['child']);

        $parentStream = new Stream($this->helper->sqlite);
        $parentStream->findByName($dep['parent']);

        $dependence = new Dependence($this->helper->sqlite);
        $dependence->setChildStream($childStream);
        $dependence->setParentStream($parentStream);

        if ($dependence->dependenceExist()) {
            msg($this->getLang('dep_exist'), -1);
        } else {
            if ($dependence->create()) {
                msg($this->getLang('dep_created'), 1);
            }
        }
    }

    public function html() {
        echo '<h1>' . $this->getLang('dependence_menu') . '</h1>';

        $streams = $this->helper->getAllStreams();
        echo $this->createDependenceFrom($streams);

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
                $dependenceStream = new Stream($dependence);
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
                $dependenceStream = new Stream($dependence);
                $dependenceStream->load();
                echo '<li>' . $dependenceStream->getName() . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<span class="badge badge-warning">Nothing</span>';
        }
    }

    /**
     * @param Stream[] $streams
     * @return string
     */
    private function createDependenceFrom(array $streams) {
        global $lang;
        $html = '<h2>' . $this->getLang('Create streams dependence') . '</h2>';
        $html .= '<div class="info">' . $this->getLang('dep_full_info') . '</div>';
        $streamNames = array_map(function (Stream $stream) {
            return $stream->getName();
        }, $streams);

        $form = new Form();
        $form->addClass('block');
        $form->setHiddenField('news[do]', 'dependence');
        $form->addDropdown('dep[parent]', $streamNames, $this->getLang('dep_parent_info'));
        $form->addDropdown('dep[child]', $streamNames, $this->getLang('dep_child_info'));
        $form->addButton('submit', $lang['btn_save']);
        $html .= $form->toHTML();
        return $html;
    }
}

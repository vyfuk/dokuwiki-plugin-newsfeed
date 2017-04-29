<?php

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

    public function getMenuText() {
        $menuText = 'News feed dependence --' . $this->getLang('dep_menu');
        return $menuText;
    }

    function getTOC() {
        return [
            ['hid' => 'dep_create', 'title' => $this->getLang('dep_create')],
            ['hid' => 'dep_list', 'title' => $this->getLang('dep_list')],
        ];
    }

    public function handle() {
        global $INPUT;
        $child = $INPUT->str('dep_child');
        $parent = $INPUT->str('dep_parent');
        if ($child == '' || $parent == '') {
            return;
        }
        $childID = $this->helper->streamToID($child);
        $parentID = $this->helper->streamToID($parent);
        $d = $this->helper->allParentDependence($parentID);
        if (in_array($childID, $d)) {
            msg($this->getLang('dep_exist'), -1);
        } else {
            $f = $this->helper->createDependence($parentID, $childID);
            if ($f) {
                msg($this->getLang('dep_created'), 1);
            }
        }
    }

    public function html() {
        $streams = $this->helper->allStream();
        ptln('<h1>' . $this->getLang('dep_menu') . '</h1>', 0);

        echo $this->createDependenceFrom($streams);

        ptln('<h2 id="dep_list">' . $this->getLang('dep_list') . ':</h2>');
        ptln('<ul>');
        foreach ($streams as $stream) {
            $stream_id = $this->helper->streamToID($stream);
            ptln('<li><h3>' . $this->getLang('stream') . ': ' . $stream . '</h3>');

            $parentDependence = $this->helper->allParentDependence($stream_id);

            $fullParentDependence = [];
            $this->helper->fullParentDependence($stream_id, $fullParentDependence);
            if (!empty($parentDependence)) {

                ptln('<span>' . $this->getLang('dep_list_parent') . '</span>');
                ptln('<ul>');
                foreach ($parentDependence as $dependence) {
                    ptln('<li>' . $this->helper->IDToStream($dependence) . '</li>');
                }
                ptln('</ul>');
                ptln('<span>' . $this->getLang('dep_list_parent_full') . '</span>');
                ptln('<ul>');

                foreach ($fullParentDependence as $dependence) {
                    echo '<li>' . $this->helper->IDToStream($dependence) . '</li>';
                }
                ptln('</ul>');
            }
            $cdep = $this->helper->allChildDependence($stream_id);
            if (!empty($cdep)) {
                ptln('<span>' . $this->getLang('dep_list_child') . '</span>');
                ptln('<ul>');
                foreach ($cdep as $d) {
                    ptln('<li>' . $this->helper->IDToStream($d) . '</li>');
                }
                ptln('</ul>');
                ptln('<span>' . $this->getLang('dep_list_child_full') . '</span>');
                ptln('<ul>');
                $fcdep = [];
                $this->helper->fullChildDependence($stream_id, $fcdep);
                foreach ($fcdep as $d) {
                    ptln('<li>' . $this->helper->IDToStream($d) . '</li>');
                }
                ptln('</ul>');
            }
            ptln('</li><hr>');
        }
        ptln('</ul>');
        ptln('</div>');
    }

    private function createDependenceFrom($streams) {
        global $lang;
        $html = '';
        $html .= '<h2 id="dep_create">' . $this->getLang('dep_create') . '</h2>';
        $html .= '<div class="info">' . $this->getLang('dep_full_info') . '</div>';

        $form = new \dokuwiki\Form\Form();
        $form->addClass('block');
        $form->setHiddenField('news_do', 'stream_dependence');
        $form->addDropdown('dep_parent', $streams, $this->getLang('dep_parent_info'));
        $form->addDropdown('dep_child', $streams, $this->getLang('dep_child_info'));
        $form->addButton('submit', $lang['btn_save']);
        $html .= $form->toHTML();
        return $html;
    }
}

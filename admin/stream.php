<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}

require_once(DOKU_PLUGIN . 'admin.php');

class admin_plugin_fksnewsfeed_stream extends DokuWiki_Admin_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 290;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText() {
        $menuText = 'FKS_newsfeed: Streams --' . $this->getLang('menu_streams');
        return $menuText;
    }

    public function handle() {
        global $INPUT;
        $streamName = $INPUT->str('stream_name');
        if ($streamName == "") {
            return;
        }
        if ($this->helper->streamToID($streamName) == 0) {
            $this->helper->createStream($streamName);
            msg('Stream has been created', 1);
        } else {
            msg('Stream already exist', -1);
        }
    }

    function getTOC() {
        return [
            ['hid' => 'stream_create', 'title' => $this->getLang('stream_create')],
            ['hid' => 'stream_list', 'title' => $this->getLang('stream_list')],
        ];
    }

    public function html() {
        global $lang;
        ptln('<h1>' . $this->getLang('manage') . '</h1>', 0);
        ptln('<h2 id="stream_create">' . 'Create stream' . '</h2>', 1);
        echo $this->newStreamForm();
        $streams = $this->helper->allStream();
        ptln('<h2 id="stream_list">Zoznam Streamov</h2>', 1);
        ptln('<ul>');
        foreach ($streams as $stream) {
            ptln('<li><span>' . $stream);
            ptln('<input type="text" class="edit" value="' . hsc('{{fksnewsfeed-stream>stream=' . $stream . ';feed=5}}') . '" />');
            ptln('</span></li>');
        }
        ptln('</ul>');
        ptln('</div>');
    }

    private function newStreamForm() {
        global $lang;
        $form = new \dokuwiki\Form\Form([
            'id' => "create_stream",
            'method' => 'POST',
            'action' => null
        ]);
        $form->setHiddenField('news_do', 'stream_add');
        $form->addTextInput('stream_name', 'názov streamu');
        $form->addButton('submit', $lang['btn_save']);
        return $form->toHTML();
    }
}

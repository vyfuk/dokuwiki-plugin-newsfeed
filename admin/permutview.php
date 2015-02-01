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

class admin_plugin_fksnewsfeed_permutview extends DokuWiki_Admin_Plugin {

    private $Rdata = array('newsdo' => null, 'newsid' => null, 'stream' => array());
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 229;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText($language) {
        $menutext = $this->getLang('permutviewmenu');
        return $menutext;
    }

    public function handle() {
        
    }

    public function html() {

        global $INPUT;

        foreach ($this->Rdata as $k => $v) {
            if ($k == 'stream') {
                $this->Rdata[$k] = $INPUT->param($k);
            } else {
                $this->Rdata[$k] = $INPUT->str($k);
                
            }
        }

        echo '<h1>' . $this->getLang('permutviewmenu') . '</h1>';
        $this->helper->FKS_helper->returnMenu('permutviewmenu');

        $this->changedstream();


        $this->getpermutnews();
    }

    private function getpermutnews() {
        global $INPUT;

        if (isset($_POST['stream-data'])) {
            $old_data = io_readFile(metaFN('fksnewsfeed:old-streams:' . $INPUT->str('stream'), '.csv'));
            $new_data = $old_data . "\n" . $INPUT->str('stream-data');
            $old_stream_path = metaFN('fksnewsfeed:old-streams:' . $INPUT->str('stream'), '.csv');

            io_saveFile($old_stream_path, $new_data);
            if (isset($_POST['stream-save'])) {
                $new_stream_path=metaFN('fksnewsfeed:streams:' . $INPUT->str('stream'), '.csv');
                io_saveFile($new_stream_path, $_POST['stream-data']);
            }
            $display = $INPUT->str('stream-data');
        } else {
            $display = io_readFile(metaFN('fksnewsfeed:streams:' . $INPUT->str('stream'), '.csv'));
        }


        $form = new Doku_Form(array('id' => "save",
            'method' => 'POST', 'action' => null));
        $form->addHidden('stream', $INPUT->str('stream'));
        $form->startFieldset('edit-stream');
        $form->addElement('<textarea name="stream-data" class="wikitext">' . $display . '</textarea>');
        $form->addElement(form_makeButton('submit', '', 'Náhľad', array()));
        $form->endFieldset();
        html_form('nic', $form);

        foreach (preg_split('/;;/', substr($display, 1, -1)) as $value) {
            $e = 'FKS_newsfeed_odd';
            $n = str_replace(array('@id@', '@even@'), array($value, $e), $this->helper->simple_tpl);
            echo p_render("xhtml", p_get_instructions($n), $info);
        }

        if (isset($_POST['stream-data'])) {
            $form = new Doku_Form(array('id' => "save",
                'method' => 'POST', 'action' => null));
            $form->addHidden('stream', $INPUT->str('stream'));
            $form->addHidden('stream-save', true);
            $form->addHidden('stream-data', $display);

            $form->startFieldset('save-stream');

            $form->addElement($display);

            $form->addElement(form_makeButton('submit', '', 'Ulož', array()));


            $form->endFieldset();
            html_form('nic', $form);
        }
    }

    private function changedstream() {

        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
        ));
        $form->startFieldset($this->getLang('changestream'));
        foreach ($this->helper->FKS_helper->filefromdir(metaFN('fksnewsfeed/streams', null)) as $value) {
            $s[] = $this->helper->shortfilename($value, 'fksnewsfeed/streams', 'NEWS_W_ID');
        }
        $form->addElement(form_makeListboxField('stream', array_merge(array(' '), $s)));
        $form->addElement(form_makeButton('submit', '', $this->getLang('changestream')));
        $form->endFieldset();
        html_form('changedirnews', $form);
    }

}

<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     
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

    private $Rdata;
    private $tableform;
    public $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');

        $this->FKS_helper = $this->loadHelper('fkshelper');
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
        
        global $Rdata;
        $this->helper->deletecache();
        $Rdata = array_merge($_POST, $_GET);
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
        //$this->helper->changedir();
        $this->helper->changedstream();
        $this->helper->lostNews($this->Rdata["dir"]);
        switch ($this->Rdata['newsdo']) {
            case "permut":
                $this->returnnewspermut($this->Rdata["dir"]);
            default:
                echo '<script type="text/javascript" charset="utf-8">'
                . 'var maxfile=' . $this->helper->findimax($this->Rdata["dir"]) . ';</script>';
                //if (isset($this->Rdata['type'])) {
                    $this->getpermutnews();
                //}
        }
    }

    private function getpermutnews() {
        $this->helper->Sdata['stream'] = $this->Rdata['stream'];
        
        $Wform = new Doku_Form(array('onsubmit'=>"return false"), null, 'POST');
        $Wform->addElement(form_makeWikiText(io_readFile(DOKU_INC . 'data/pages/fksnewsfeed/streams/' . $this->helper->Sdata['stream'] . '.csv')));
        $Wform->addElement(form_makeButton('button', 'Ulož'));
        $Wform->addElement(form_makeButton('button', 'Náhled'));
        html_form('nic', $Wform);


        $this->tableform = new Doku_Form(array('method' => "post", 'id' => "fksnewsadminperm"));

        $this->tableform->startFieldset($this->getLang('permutmenu'));
        $this->tableform->addElement($this->helper->addlocation($this->Rdata));

        $this->tableform->addElement($this->FKS_helper->returnmsg('<p><span style="font-weight:bold;font-size:130%">'
                        . $this->getLang('permwarning1')
                        . '</span></p>'
                        . '<p>'
                        . $this->getLang('permwarning2')
                        . '</p>'
                        , -1));
        $this->tableform->endFieldset();
        $this->tableform->addElement(form_makeOpenTag('div', array('class' => 'fks_news_permut')));
        //$this->tableform->addHidden("maxnews", $this->helper->findimax($dir));
        $this->tableform->addHidden("newsdo", "permut");
        switch ($this->Rdata['type']) {
            case'stream':
                $this->tableform->addHidden('type', 'stream');
                $this->tableform->addHidden('stream', $this->Rdata['stream']);
                break;
            case'dir':

                $this->tableform->addHidden('type', 'dir');
                $this->tableform->addHidden('dir', $this->Rdata['dir']);
                break;
        }

        $this->tableform->addElement(form_makeOpenTag('table', array('class' => 'newspermuttable')));
        $this->makeTablehead();



        $this->getnewstr(null);
        $i = 1;

        foreach ($this->helper->loadstream($this->Rdata) as $key => $value) {
            if (isset($this->Rdata['stream'])) {
                list($id, $dir) = preg_split('/-/', $value);
                $this->getnewstr(array_merge(
                                $this->helper->extractParamtext_feed(substr(io_readFile($this->helper->getnewsurl(array(
                                                            'dir' => $dir,
                                                            'id' => $id))), 13, -16)), array(
                    'dir' => $dir,
                    'id' => $id,
                    'trno' => $i,
                    'type' => 'stream')));
            } else {
                $this->getnewstr(array_merge(
                                $this->helper->extractParamtext_feed(io_readFile($this->helper->getnewsurl(array(
                                                    'dir' => $this->Rdata['dir'],
                                                    'id' => $value)
                                ))), array(
                    'dir' => $this->Rdata['dir'],
                    'id' => $value,
                    'trno' => $i,
                    'type' => 'dir')));
            }
            $i++;
        }
        $this->tableform->addElement(form_makeCloseTag('table'));
        $this->tableform->startFieldset(null);
        $this->tableform->addElement(form_makeButton('submit', '', $this->getLang('newssave')));
        $this->tableform->endFieldset();
        $this->tableform->addElement(form_makeCloseTag('div'));
        html_form('table', $this->tableform);
    }

    private function getnewstr($data) {


        $this->tableform->addElement(form_makeOpenTag('tr', array('class' => 'fksnewstr')));
        $this->tableform->addElement(form_makeOpenTag('td', array('class' => "fksnewsid", 'id' => "fks_news_i" . $data['trno'])));
        $this->tableform->addElement('<span>' . $data['trno'] ++ . '</span>');
        $this->tableform->addElement(form_makeCloseTag('td'));
        $this->tableform->addElement(form_makeOpenTag('td', array('class' => "fksnewspermnew", 'id' => "fks_news_admin_perm_new" . $i)));
        $this->tableform->addElement(form_makeDatalistField("newson" . $data['trno'], 'fks_news_admin_permut_new_input' . $data['trno'], $this->helper->allNews(), '', 'fksnewsinputperm', array('value' => $data['id'])));
        $this->tableform->addElement(form_makeCloseTag('td'));
        $this->tableform->addElement(form_makeOpenTag('td'), array('class' => "fksnewsdirstream", 'id' => 'fks_dir_stream'));
        $this->tableform->addElement(form_textfield(array(/* 'readonly' => 'readonly', */ 'name' => 'newsdiron' . $data['trno'], 'value' => $data['dir'])));
        $this->tableform->addElement(form_makeCloseTag('td'));
        $this->tableform->addElement(form_makeOpenTag('td'), array('id' => "fks_news_admin_view" . $data['trno']));
        $this->tableform->addElement(form_makeListboxField('newsonR' . $data['trno'], array(
            array("T", $this->getLang('display')),
            array('F', $this->getLang('nodisplay'))
                        ), '', ''));
        $this->tableform->addElement(form_makeCloseTag('td'));
        $this->tableform->addElement(form_makeOpenTag('td', array('class' => "fks_news_info", 'id' => 'fks_news_admin_info' . $data['trno'])));
        $this->tableform->addElement(form_makeOpenTag('span', array('id' => 'fks_news_admin_info' . $data['trno'] . '_span', 'style' => 'color:#000')));
        $this->tableform->addElement($this->helper->shortName($data['name'], 25));
        $this->tableform->addElement(form_makeCloseTag('span'));
        $this->tableform->addElement(form_makeCloseTag('td'));
        $this->tableform->addElement(form_makeCloseTag('tr'));
        $this->makeTRdiv($data);
    }

    private function returnnewspermut() {

        echo $this->helper->controlData($this->Rdata);
    }

    private function makeTablehead() {

        $this->tableform->addElement(form_makeOpenTag('thead'));
        $this->tableform->addElement(form_makeOpenTag('tr'));
        $this->tableform->addElement(form_makeOpenTag('th'));
        $this->tableform->addElement($this->getLang('newspermold'));
        $this->tableform->addElement(form_makeCloseTag('th'));

        $this->tableform->addElement(form_makeOpenTag('th'));
        $this->tableform->addElement($this->getLang('IDnews'));
        $this->tableform->addElement(form_makeCloseTag('th'));

        $this->tableform->addElement(form_makeOpenTag('th'));
        $this->tableform->addElement($this->getLang('dir'));
        $this->tableform->addElement(form_makeCloseTag('th'));

        $this->tableform->addElement(form_makeOpenTag('th'));
        $this->tableform->addElement($this->getLang('newrender'));
        $this->tableform->addElement(form_makeCloseTag('th'));

        $this->tableform->addElement(form_makeOpenTag('th'));
        $this->tableform->addElement($this->getLang('newsname'));
        $this->tableform->addElement(form_makeCloseTag('th'));

        $this->tableform->addElement(form_makeCloseTag('tr'));
        $this->tableform->addElement(form_makeCloseTag('thead'));
    }

    private function makeTRdiv($data) {

        $this->tableform->addElement(form_makeOpenTag('div', array('class' => 'fksnewsmoreinfo', 'id' => 'fks_news_admin_info' . $data['trno'] . '_div', 'style' => ' ')));
        $this->tableform->addElement($this->getLang('author') . ': ' . $data['author'] . '<br>'
                . $this->getLang('email') . ': ' . $data['email'] . '<br>'
                . $this->getLang('date') . ': ' . $data['newsdate']);
        $this->tableform->addElement(form_makeOpenTag('div', array('class' => 'fksnewsmoreinfotext')));
        $this->tableform->addElement($data["text-html"]);
        $this->tableform->addElement(form_makeCloseTag('div'));
        $this->tableform->addElement(form_makeCloseTag('div'));
    }

}

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
        global $lang;
    }

    public function html() {
        global $lang;
        global $conf;

        $this->helper->deletecache();
        global $Rdata;
        $Rdata = $_POST;

        $this->helper->returnMenu('permutviewmenu');
        $this->helper->changedir();
        $this->helper->changedstream();
        $this->helper->lostNews($Rdata["dir"]);
        switch ($Rdata['newsdo']) {
            case "permut":
                $this->returnnewspermut($Rdata["dir"]);
            default:

                echo '<script type="text/javascript" charset="utf-8">'
                . 'var maxfile=' . $this->helper->findimax($Rdata["dir"]) . ';</script>';
                if (isset($Rdata['type'])) {
                    $this->getpermutnews($Rdata["dir"]);
                }
        }
    }

    private function getpermutnews($dir) {
        global $Rdata;
        global $lang;

        global $tableform;

        $tableform = new Doku_Form(array('method' => "post", 'id' => "fksnewsadminperm"));

        $tableform->startFieldset($this->getLang('permutmenu'));
        $tableform->addElement($this->helper->addlocation($Rdata));

        $tableform->addElement($this->helper->returnmsg('<p><span style="font-weight:bold;font-size:130%">'
                        . $this->getLang('permwarning1')
                        . '</span></p>'
                        . '<p>'
                        . $this->getLang('permwarning2')
                        . '</p>'
                        , -1));
        $tableform->endFieldset();
        $tableform->addElement(form_makeOpenTag('div', array('class' => 'fks_news_permut')));
        $tableform->addHidden("maxnews", $this->helper->findimax($dir));
        $tableform->addHidden("newsdo", "permut");
        switch ($Rdata['type']) {
            case'stream':
                $tableform->addHidden('type', 'stream');
                $tableform->addHidden('stream', $Rdata['stream']);
                break;
            case'dir':

                $tableform->addHidden('type', 'dir');
                $tableform->addHidden('dir', $dir);
                break;
        }

        $tableform->addElement(form_makeOpenTag('table', array('class' => 'newspermuttable')));
        $this->makeTablehead();



        $this->getnewstr(null);
        $i = 1;
        foreach ($this->helper->loadstream($Rdata) as $key => $value) {
            if (isset($Rdata['stream'])) {
                list($id, $dir) = preg_split('/-/', $value);
                $this->getnewstr(array_merge(
                                $this->helper->extractParamtext(substr(io_readFile($this->helper->getnewsurl(array(
                                                            'dir' => $dir,
                                                            'id' => $id))), 13, -16)), array(
                    'dir' => $dir,
                    'id' => $id,
                    'trno' => $i,
                    'type' => 'stream')));
            } else {
                $this->getnewstr(array_merge(
                                $this->helper->extractParamtext(io_readFile($this->helper->getnewsurl(array(
                                                    'dir' => $Rdata['dir'],
                                                    'id' => $value)
                                ))), array(
                    'dir' => $Rdata['dir'],
                    'id' => $value,
                    'trno' => $i,
                    'type' => 'dir')));
            }
            $i++;
        }
        $tableform->addElement(form_makeCloseTag('table'));
        $tableform->startFieldset(null);
        $tableform->addElement(form_makeButton('submit', '', $this->getLang('newssave')));
        $tableform->endFieldset();
        $tableform->addElement(form_makeCloseTag('div'));
        html_form('table', $tableform);
    }

    private function getnewstr($data) {
        /** @var je poradie $i */
        /** @var je ID novinky $no  */
        $i = $data['trno'];
        //print_r($data["name"]);
        $data["shortname"] = $this->helper->shortName($data['name'], 25);
        global $lang;
        global $tableform;
        $tableform->startTR(array('class' => 'fksnewstr'));
        $tableform->startTD(array('class' => "fksnewsid", 'id' => "fks_news_i" . $i));
        $tableform->addElement('<span>' . $i ++ . '</span>');
        $tableform->endTD();
        $tableform->startTD(array('class' => "fksnewspermnew", 'id' => "fks_news_admin_perm_new" . $i));
        $tableform->addElement(form_makeDatalistField("newson" . $i, 'fks_news_admin_permut_new_input' . $i, $this->helper->allNews($dir), '', 'fksnewsinputperm', array('value' => $data['id'])));
        $tableform->endTD();

        //$tableform->startTD(array('class' => "fksnewsimage"));
        //$tableform->addElement('<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/up.gif" class="fks_news_admin_up">'
        //        . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/down.gif" class="fks_news_admin_down">');
        //$tableform->endTD();

        $tableform->startTD(array('class' => "fksnewsdirstream", 'id' => 'fks_dir_stream'));
        $tableform->addElement(form_textfield(array(/* 'readonly' => 'readonly', */ 'name' => 'newsdiron' . $i, 'value' => $data['dir'])));
        $tableform->endTD();
        $tableform->startTD(array('id' => "fks_news_admin_view" . $i));
        $tableform->addElement(form_makeListboxField('newsonR' . $i, array(
            array("T", $this->getLang('display')),
            array('F', $this->getLang('nodisplay'))
                        ), '', ''));
        $tableform->endTD();
        $tableform->startTD(array('class' => "fks_news_info", 'id' => 'fks_news_admin_info' . $i));
        $tableform->addElement(form_makeOpenTag('span', array('id' => 'fks_news_admin_info' . $i . '_span', 'style' => 'color:#000')));
        $tableform->addElement($data['shortname']);
        $tableform->addElement(form_makeCloseTag('span'));
        $tableform->endTD();
        $tableform->endTR();
        $tableform->addElement(form_makeOpenTag('div', array('class' => 'fksnewsmoreinfo', 'id' => 'fks_news_admin_info' . $i . '_div', 'style' => ' ')));
        $tableform->addElement($this->getLang('author') . ': ' . $data['author'] . '<br>'
                . $this->getLang('email') . ': ' . $data['email'] . '<br>'
                . $this->getLang('date') . ': ' . $data['newsdate']);
        $tableform->addElement(form_makeOpenTag('div', array('class' => 'fksnewsmoreinfotext')));
        $tableform->addElement($data["text-html"]);
        $tableform->addElement(form_makeCloseTag('div'));
        $tableform->addElement(form_makeCloseTag('div'));
    }

    private function returnnewspermut() {
        global $lang;
        global $Rdata;
        echo $this->helper->controlData();
    }

    private function makeTablehead() {
        global $tableform;
        $tableform->addElement(form_makeOpenTag('thead'));
        $tableform->addElement(form_makeOpenTag('tr'));
        $tableform->addElement(form_makeOpenTag('th'));
        $tableform->addElement($this->getLang('newspermold'));
        $tableform->addElement(form_makeCloseTag('th'));

        $tableform->addElement(form_makeOpenTag('th'));
        $tableform->addElement($this->getLang('IDnews'));
        $tableform->addElement(form_makeCloseTag('th'));

        $tableform->addElement(form_makeOpenTag('th'));
        $tableform->addElement($this->getLang('dir'));
        $tableform->addElement(form_makeCloseTag('th'));

        $tableform->addElement(form_makeOpenTag('th'));
        $tableform->addElement($this->getLang('newrender'));
        $tableform->addElement(form_makeCloseTag('th'));

        $tableform->addElement(form_makeOpenTag('th'));
        $tableform->addElement($this->getLang('newsname'));
        $tableform->addElement(form_makeCloseTag('th'));

        $tableform->addElement(form_makeCloseTag('tr'));
        $tableform->addElement(form_makeCloseTag('thead'));
    }

}

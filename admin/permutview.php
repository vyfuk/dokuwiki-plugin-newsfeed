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
        switch ($Rdata['newsdo']) {
            case "permut":
                $this->returnnewspermut();
            default:
                echo '<script type="text/javascript" charset="utf-8">'
                . 'var maxfile=' . $this->helper->findimax() . ';</script>';
                $this->getpermutnews();
                $this->helper->lostNews();
        }
    }

    private function getpermutnews() {
        global $lang;
        
        global $tableform;

        $tableform = new Doku_Form(array('method' => "post", 'id' => "fksnewsadminperm"));

        $tableform->startFieldset($this->getLang('permutmenu'));
        $tableform->endFieldset();

        ob_start();
        $this->getnewswarning();
        $W = ob_get_contents();
        ob_end_clean();
        $tableform->addElement($W);
        $tableform->addElement('<div class="fks_news_permut">');
        $tableform->addHidden("maxnews", $this->helper->findimax());
        $tableform->addHidden("newsdo", "permut");
        $tableform->addElement('<table class="newspermuttable">');

        $tableform->addElement('<thead><tr><th>' . $this->getLang('newspermold') . '</th>'
                . '<th>' . $this->getLang('IDnews') . '</th>'
                . '<th></th>'
                . '<th>' . $this->getLang('newrender') . '</th>'
                . '<th class="fksnewsinfo">' . $this->getLang('newsname') . '</th></tr></thead>');

        $this->getnewstr(0, null);

        $i = 1;
        $Sdata=array();
        print_r($this->helper->loadstream($Sdata));
        foreach ($this->helper->loadstream($Sdata) as $key => $value) {
           /// list($no) = preg_split('/-/', $value);
            $this->getnewstr($i, $this->helper->getfulldata($value,$Sdata));
            $i++;
        }
        $tableform->addElement('</table>');
        $tableform->startFieldset(null);

        $tableform->addElement(form_makeButton('submit', '', $this->getLang('newssave')));
        $tableform->endFieldset();
        //print_r($tableform);

        html_form('table', $tableform);
    }

    private function getnewstr($i, $data) {
        /** @var je poradie $i */
        /** @var je ID novinky $no  */
        global $lang;
        global $tableform;

        
        $tableform->startTR(array('class' => 'fksnewstr'));

        $tableform->startTD(array('class' => "fksnewsid", 'id' => "fks_news_i" . $i));
        $tableform->addElement($i + 1);
        $tableform->endTD();


        $tableform->startTD(array('class' => "fksnewspermnew", 'id' => "fks_news_admin_perm_new" . $i));
        $tableform->addElement(form_makeDatalistField("newson" . $i, 'fks_news_admin_permut_new_input' . $i, $this->helper->allNews(), '', 'fksnewsinputperm', array('value' => $data['id'])));
        $tableform->endTD();

        $tableform->startTD(array('class' => "fksnewsimage"));
        $tableform->addElement('<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/up.gif" class="fks_news_admin_up">'
                . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/down.gif" class="fks_news_admin_down">');
        $tableform->endTD();

        $tableform->startTD(array('id' => "fks_news_admin_view" . $i));
        $tableform->addElement(form_makeListboxField('newsonR' . $i, array(
            array("T", $this->getLang('display')),
            array('F', $this->getLang('nodisplay'))
                        ), '', ''));
        $tableform->endTD();

        $tableform->startTD(array('class' => "fks_news_info", 'id' => 'fks_news_admin_info' . $i));
        $tableform->addElement('<span id="fks_news_admin_info' . $i . '_span" style="color:#000">'
                . $data['shortname'] . '</span>');
        $tableform->endTD();

        $tableform->endTR();
        $tableform->addElement('<div class="fksnewsmoreinfo" id="fks_news_admin_info' . $i . '_div" style=" " >'
                . $this->getLang('author') . ': ' . $data['author'] . '<br>'
                . $this->getLang('email') . ': ' . $data['email'] . '<br>'
                . $this->getLang('date') . ': ' . $data['newsdate']
                . '<div class="fksnewsmoreinfotext">'
                . $data["text-html"]
                . '</div>'
                . '</div>');
    }

    private function getnewswarning() {
        global $lang;

        msg('<p><span style="font-weight:bold;font-size:130%">'
                . $this->getLang('permwarning1')
                . '</span></p>'
                . '<p>'
                . $this->getLang('permwarning2')
                . '</p>'
                , -1);
        return true;
    }

    private function returnnewspermut() {
        global $lang;
        global $Rdata;
        //print_r($Rdata);


        echo $this->helper->controlData();
    }

}

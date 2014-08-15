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

class admin_plugin_fksnewsfeed_addedit extends DokuWiki_Admin_Plugin {

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
        $menutext = $this->getLang('addeditmenu');
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
        $this->helper->returnMenu('addeditmenu');
        switch ($Rdata['newsdo']) {
            case "add":
                $this->returnnewsadd();
            default:
                $this->getaddnews();
                $this->geteditnews();
        }
    }

    private function geteditnews() {
        global $conf;
        $Fform = new Doku_Form(array());
        $Fform->addElement(makeHeading($this->getLang('editmenu'), array(), 2));
        $Fform->endFieldset();
        html_form('fform', $Fform);

        $allnews = glob($this->helper->getnewsurl("*"));
        $arraynews = array();
        foreach ($allnews as $key => $value) {
            $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
            $idn = substr($value, count($this->helper->getnewsurl("*")) - 6, -4);
            $form->startFieldset($value);
            $form->endFieldset();
            $form->addElement('<div class="fksnewswrapper">'
                    . $this->helper->rendernews($idn, 'fksnewseven')
                    . '</div>');
            $form->addHidden("do", "edit");
            $form->addHidden('id', $this->helper->getwikinewsurl($idn));
            $form->addHidden("target", "plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));

            ob_start();
            html_form('editnews', $form);
            $to_page = "";
            $to_page.=ob_get_contents();
            ob_end_clean();
            $arraynews[] = $to_page;
        }
        $arraynews = array_reverse($arraynews, false);
        foreach ($arraynews as $key => $value) {
            echo $value;
        }
        echo '</div>';
    }

    private function getaddnews() {
        global $lang;
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'class' => 'fksreturn'));
        $form->addElement(makeHeading($this->getLang('addmenu'), array(), 2));
        $form->startFieldset($this->getLang('addmenu'));
        /*
         * vysranie s blbým msg()
         */
        ob_start();
        msg($this->getLang('addnews') . ' ' . $this->helper->findimax(), 1);
        $msg_TP = ob_get_contents();
        ob_end_clean();
        $form->addElement($msg_TP);

        $form->addHidden("newsdo", "add");
        $form->addHidden('newsid', $this->helper->findimax());
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
        $form->endFieldset();
        html_form('addnews', $form);
    }

    private function returnnewsadd() {
        global $Rdata;
        global $lang;
        $newsID = io_readFile("data/meta/newsfeed.csv", FALSE);
        $newsID = ';' . $Rdata['newsid'] . ";" . $newsID;
        $Wdata = file_put_contents("data/meta/newsfeed.csv", $newsID);

        $Wnews = $this->helper->saveNewNews($Rdata);
        if ($Wdata && $Wnews) {
            msg(' written successful', 1);
        } else {
            msg("written failure", -1);
        }

        msg($this->getLang('autoreturn'), -1);
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'action' => DOKU_BASE, 'class' => 'fksreturn'));
        $form->addHidden('do', "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('id', $this->helper->getwikinewsurl($Rdata['newsid']));

        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddwikinews')));
        html_form('addnews', $form);
    }

}

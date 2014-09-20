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
        global $Rdata;


        $this->helper->deletecache();

        $Rdata = $_POST;
        /* if (!isset($Rdata['dir'])) {
          $Rdata['dir'] = 'start';
          } */
        $this->helper->returnMenu('addeditmenu');
        $this->helper->changedir();


        switch ($Rdata['newsdo']) {
            case "add":
                $this->returnnewsadd($Rdata['dir']);
            default:
                if (isset($Rdata['type'])) {
                    $this->getaddnews($Rdata['dir']);
                    $this->geteditnews($Rdata['dir']);
                }
        }
    }

    private function geteditnews() {
        global $Rdata;
        global $conf;
        $Fform = new Doku_Form(array());
        $Fform->addElement(makeHeading($this->getLang('editmenu'), array(), 2));
        $Fform->addElement($this->helper->addlocation($Rdata));
        $Fform->endFieldset();
        html_form('fform', $Fform);

        $arraynews = array();


        foreach (glob($this->helper->getnewsurl(array('id' => "*", 'dir' => $Rdata['dir']))) as $key => $value) {
            $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
            $form->startFieldset(substr($value, strlen(DOKU_INC."data/pages/fksnewsfeed/" . $Rdata['dir'] . "/"), -4));
            $form->endFieldset();

            $form->addElement('<div class="fksnewswrapper">'
                    . $this->helper->renderfullnews(array('dir' => $Rdata['dir'], 'id' => substr(str_replace(DOKU_INC, '', $value), strlen("data/pages/fksnewsfeed/" . $Rdata['dir'] . "/news"), -4), 'even' => 'fksnewseven'))
                    . '</div>');
            $form->addHidden('dir', $Rdata['dir']);
            $form->addHidden("do", "edit");
            $form->addHidden('id', $this->helper->getwikinewsurl(array('id' => substr(str_replace(DOKU_INC, '', $value), strlen("data/pages/fksnewsfeed/" . $Rdata['dir'] . "/news"), -4), 'dir' => $Rdata['dir'])));
            $form->addHidden("target", "plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));
            ob_start();
            html_form('editnews', $form);
            $arraynews[] = ob_get_contents();
            ob_end_clean();
        }
        foreach (array_reverse($arraynews, false) as $key => $value) {
            echo $value;
        }
        echo '</div>';
    }

    private function getaddnews() {
        global $lang;
        global $Rdata;
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'class' => 'fksreturn'));
        $form->addElement(makeHeading($this->getLang('addmenu'), array(), 2));
        $form->startFieldset($this->getLang('addmenu'));
        $form->addElement($this->helper->addlocation($Rdata));
        $form->addElement($this->helper->returnmsg($this->getLang('addnews') . ' ' . $this->helper->findimax($Rdata['dir']), 0));
        $form->addHidden('dir', $Rdata['dir']);
        $form->addHidden("newsdo", "add");
        $form->addHidden('newsid', $this->helper->findimax($Rdata['dir']));
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
        $form->endFieldset();
        html_form('addnews', $form);
    }

    private function returnnewsadd() {
        global $Rdata;
        global $lang;
        $Wdata = file_put_contents(DOKU_INC . "data/pages/fksnewsfeed/" . $Rdata['dir'] . "/newsfeed.csv", ';' . $Rdata['newsid'] . ";" . io_readFile("data/pages/fksnewsfeed/" . $Rdata['dir'] . "/newsfeed.csv", FALSE));

        $Wnews = $this->helper->saveNewNews($Rdata);
        if ($Wdata && $Wnews) {
            msg(' written successful', 1);
        } else {
            msg("written failure", -1);
        }

        msg($this->getLang('autoreturn'), -1);
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'action' => DOKU_BASE, 'class' => 'fksreturn'));
        $form->addHidden('dir', $Rdata['dir']);
        $form->addHidden('do', "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('id', $this->helper->getwikinewsurl(array('id' => $Rdata['newsid'], 'dir' => $Rdata['dir'])));

        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddwikinews')));
        html_form('addnews', $form);
    }

}

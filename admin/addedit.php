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

    private $Rdata = array('newsdo' => null, 'newsid' => null, 'stream' => array());

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
        $menutext = $this->getLang('addeditmenu');
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

        //unset($this->Rdata);
        $this->Rdata['dir'] = 'feeds';

        echo '<h1>' . $this->getLang('addmenu') . '</h1>';
        $this->helper->FKS_helper->returnMenu();

        switch ($this->Rdata['newsdo']) {
            case "add":
                $this->returnnewsadd();
            default:

                $this->getaddnews();
                $this->geteditnews();
        }
    }

    private function geteditnews() {
        echo '<h2>' . $this->getLang('editmenu') . '</h2>';
        
        foreach (array_reverse($this->helper->allshortnews($this->Rdata), FALSE) as $value) {
            $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
            $form->startFieldset($this->helper->shortfilename($value, 'fksnewsfeed/feeds', $flag = 'NEWS_W_ID'));
            $form->endFieldset();
            $form->addElement(form_makeOpenTag('div', array('class' => 'fksnewswrapper')));
            
            $form->addElement($this->helper->renderfullnews($this->helper->shortfilename($value, 'fksnewsfeed/feeds', 'ID_ONLY')));
            $form->addElement(form_makeCloseTag('div'));
            $form->addHidden("do", "edit");
            $form->addHidden('id', $this->helper->getwikinewsurl($this->helper->shortfilename($value, 'fksnewsfeed/feeds', 'ID_ONLY')));
            $form->addHidden("target", "plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));
            html_form('editnews', $form);
        }
    }

    private function getaddnews() {
        echo '<h2>' . $this->getLang('addmenu') . '</h2>';
        $form = new Doku_Form(array('method' => 'POST'));
        $form->addElement($this->helper->FKS_helper->returnmsg($this->getLang('addnews') . ' ' . $this->helper->findimax('feeds'), 0));
        $form->addHidden("newsdo", "add");
        foreach (helper_plugin_fkshelper::filefromdir(metaFN('fksnewsfeed/streams',null)) as $value) {
            
            $form->addElement(form_makeCheckboxField("stream[" . $this->helper->shortfilename($value, 'fksnewsfeed/streams', 'NEWS_W_ID') . "]", 1, $this->helper->shortfilename($value, 'fksnewsfeed/streams', 'NEWS_W_ID')));
        }
        $form->addHidden('newsid', $this->helper->findimax('feeds'));
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
        html_form('addnews', $form);
    }

    private function returnnewsadd() {
        foreach ($this->Rdata["stream"] as $k => $v) {
            if ($k) {
                $c = '';
                $c.=';' . $this->Rdata['newsid'] . ";";
                $c.=io_readFile(metaFN('fksnewsfeed/streams/' . $k , ".csv"), FALSE);
                if (io_saveFile(metaFN('fksnewsfeed/streams/' . $k , ".csv"), $c)) {
                    msg(' written to ' . $k . ' successful', 1);
                } else {
                    msg("written to '.$k.' failure", -1);
                }
            }
            //var_dump(io_readFile("data/pages/fksnewsfeed/streams/" . $k . ".csv", FALSE));
        }


        global $INFO;
        $Wnews = $this->helper->saveNewNews(array('author' => $INFO['userinfo']['name'],
            'newsdate' => dformat(),
            'email' => $INFO['userinfo']['mail'],
            'text' => 'Tady napiš text aktuality',
            'name' => 'Název aktuality'), $this->Rdata['newsid']);
        if ($Wnews) {
            msg('written into new nwes successful', 1);
        } else {
            msg("written into new nwes failure", -1);
        }

        //msg($this->getLang('autoreturn'), -1);
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'action' => DOKU_BASE, 'class' => 'fksreturn'));
        $form->addHidden('do', "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('id', $this->helper->getwikinewsurl($this->Rdata['newsid']));

        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddwikinews')));
        html_form('addnews', $form);
    }

}

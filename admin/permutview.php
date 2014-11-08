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

    private $Rdata = array('newsdo' => null, 'newsid' => null, 'stream' => array());

    private $tableform;
    private $helper;

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
        $this->changedstream();
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
        $this->getdeletenews();
        
        $Wform = new Doku_Form(array('onsubmit'=>"return false"), null, 'POST');
        $Wform->addElement(form_makeWikiText(io_readFile(DOKU_INC . 'data/pages/fksnewsfeed/streams/' . $this->helper->Sdata['stream'] . '.csv')));
        $Wform->addElement(form_makeButton('button', 'Ulož'));
        $Wform->addElement(form_makeButton('button', 'Náhled'));
        html_form('nic', $Wform);


        
        
        
        
    }

   

    private function returnnewspermut() {

        echo $this->helper->controlData($this->Rdata);
    }

    
    private function changedstream() {
        
        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
        ));
        $form->startFieldset($this->getLang('changestream'));
        //$form->addElement(form_makeDatalistField('stream', 'stream', $this->helper->allstream(), $this->getLang('stream')));
        $form->addHidden('type', 'stream');
        $form->addElement(form_makeButton('submit', '', $this->getLang('changestream')));
        $form->endFieldset();
        //var_dump($form);
        html_form('changedirnews', $form);
    }
    
    private function getdeletenews() {
        echo '<h2>' . $this->getLang('editmenu') . '</h2>';

        foreach (array_reverse($this->helper->loadstream($this->Rdata['stream']), FALSE) as $value) {
            $form = new Doku_Form(array('id' => 'deletenews', 'method' => 'POST', 'class' => 'fksreturn'));
            $form->startFieldset($this->helper->shortfilename($value, 'fksnewsfeed/feeds', $flag = 'NEWS_W_ID'));
            $form->endFieldset();
            $form->addElement(form_makeOpenTag('div', array('class' => 'fksnewswrapper')));

            $form->addElement(p_render('xhtml',  p_get_instructions('<fksnewsfeed id='.$value.'>'),$info));
            $form->addElement(form_makeCloseTag('div'));
            $form->addHidden("newsdo", "newsdelete");
            //$form->addHidden('id', $this->helper->getwikinewsurl($this->helper->shortfilename($value, 'fksnewsfeed/feeds', 'ID_ONLY')));
            //$form->addHidden("target", "plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));
            html_form('editnews', $form);
        }
    }

}

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
        $menutext = $this->getLang('menu');
        return $menutext;
    }

    public function handle() {
        global $lang;
    }

    public function html() {
        global $lang;
        global $conf;

        $this->helper->deletecache();
        global $newsreturndata;
        $newsreturndata = $_POST;

        switch ($newsreturndata['newsdo']) {
            case "add":
                $this->returnnewsadd();
                break;

            case "delete":
                $this->returnnewsdelete();

                break;
            case "permut":
                $this->returnnewspermut();

                break;
            default:
                /*
                 * add news
                 */
                $this->getaddnews();
                /*
                 * permutation news
                 */

                $this->geteditnews();
        }
    }

    private function geteditnews() {
        $allnews = glob($this->helper->getnewsurl("*"));
        $arraynews = array();
        foreach ($allnews as $key => $value) {
            $idn = substr($value, count($this->helper->getnewsurl("*")) - 6, -4);
            $to_page = "";
            $to_page.= '<div class="fksnewswrapper">
             ' . $this->helper->rendernews($idn, 'fksnewseven') . '
                    </div>';
            $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
            $form->addHidden("do", "edit");
            $form->addHidden('id', $this->helper->getwikinewsurl($idn));
            $form->addHidden("target", "plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));
            ob_start();
            html_form('editnews', $form);
            $to_page.=ob_get_contents();
            ob_end_clean();
            $arraynews[] = $to_page;
        }
        $arraynews = array_reverse($arraynews, false);
        foreach ($arraynews as $key => $value) {
            echo $value;
        }
    }

    private function getaddnews() {
        global $lang;
        echo '<h1 class="fkshover">' . $this->getLang('addmenu') . '</h1>';

        echo '<div class="fks_news_add">';
        echo '<div class="info">';
        echo '<span> ' . $this->getLang('addnews') . ' ' . $this->helper->findimax() . '</span>';
        echo '</div>';

        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'class' => 'fksreturn'));
        $form->addHidden("newsdo", "add");
        $form->addHidden('newsid', $imax);
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
        html_form('addnews', $form);
        echo '</div>';
    }

    private function returnnewsadd() {
        global $imax;
        global $newsreturndata;
        global $lang;
        global $INFO;

        $newsID = io_readFile("data/meta/newsfeed.csv", FALSE);
        $newsID = ';' . $newsreturndata['newsid'] . "-T;" . $newsID;
        file_put_contents("data/meta/newsfeed.csv", $newsID);

        $this->helper->saveNewNews($newsreturndata);

        $newsurlnew = $this->helper->getwikinewsurl($newsreturndata['newsid']);
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'action' => DOKU_BASE, 'class' => 'fksreturn'));
        $form->addHidden('do', "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('id', $newsurlnew);
        $form->addElement('<div class="error"><p>' . $this->getLang('autoreturn') . '</p></div>');
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddwikinews')));
        html_form('addnews', $form);
    }

}

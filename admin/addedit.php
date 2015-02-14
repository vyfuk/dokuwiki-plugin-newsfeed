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

class admin_plugin_fksnewsfeed_addedit extends DokuWiki_Admin_Plugin {

    private $Rdata = array('newsdo' => null, 'newsid' => null, 'stream' => array(), 'add_stream' => null);

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
        $menutext = $this->getLang('add_n_edit_menu');
        return $menutext;
    }

    public function handle() {
        
    }

    public function html() {
        global $INPUT;



        echo '<h1>' . $this->getLang('add_menu') . '</h1>';
        $this->helper->FKS_helper->returnMenu();
        switch ($INPUT->str('newsdo')) {
            case "add":
                $this->returnnewsadd();
            default:
                $this->getaddnews();
                $this->geteditnews();
        }
    }

    private function geteditnews() {
        echo '<h2>' . $this->getLang('edit_menu') . '</h2>';
        foreach (array_reverse($this->helper->allshortnews(), FALSE) as $value) {
            echo '<legend>' . $this->helper->shortfilename($value, 'fksnewsfeed/feeds', 'NEWS_W_ID') . '</legend>';
            $id = $this->helper->shortfilename($value, 'fksnewsfeed/feeds', 'ID_ONLY');
            $n = str_replace(array('@id@', '@even@'), array($id, 'FKS_newsfeed_even'), $this->helper->simple_tpl);
            echo p_render("xhtml", p_get_instructions($n), $info);
        }
    }

    private function returnnewsadd() {
        global $INFO;
        global $INPUT;
        $this->helper->_log_event('add', $INPUT->str('newsid'));
        $Wnews = $this->helper->saveNewNews(array('author' => $INFO['userinfo']['name'],
            'newsdate' => dformat(),
            'email' => $INFO['userinfo']['mail'],
            'text' => 'Tady napiš text aktuality',
            'name' => 'Název aktuality'), $this->helper->getwikinewsurl($INPUT->str('newsid')));
        if ($Wnews) {
            msg('written into new news successful', 1);
            if (!empty($INPUT->str("stream"))) {
                foreach ($INPUT->param("stream") as $k => $v) {
                    if ($k) {
                        $c = '';
                        $c.=';' . $INPUT->str('newsid') . ";";
                        $c.=io_readFile(metaFN('fksnewsfeed/streams/' . $k, ".csv"), FALSE);
                        if (io_saveFile(metaFN('fksnewsfeed/streams/' . $k, ".csv"), $c)) {
                            msg(' written to ' . $k . ' successful', 1);
                        } else {
                            msg("written to '.$k.' failure", -1);
                        }
                    }
                }
            }
        } else {
            msg("written into new news failure", -1);
        }
        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'action' => DOKU_BASE, 'class' => 'fksreturn'));
        $form->addHidden('do', "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addHidden('id', $this->helper->getwikinewsurl($INPUT->str('newsid')));

        $form->addElement(form_makeButton('submit', '', $this->getLang('btn_get_add_news')));
        html_form('addnews', $form);
    }

    private function getaddnews($stream = null) {
        global $INPUT;

        echo '<h2>' . $this->getLang('add_menu') . '</h2>';
        $form = new Doku_Form(array('method' => 'POST'));
        $msg = $this->getLang('add_news') . ' ' . $this->helper->findimax('feeds');
        $form->addElement($this->helper->FKS_helper->returnmsg($msg, 0));
        $form->addHidden("news_do", "add");
        $allstream = helper_plugin_fksnewsfeed::allstream();
        foreach ($allstream as $k => $value) {
            $select = null;
            if (empty($INPUT->str('add_stream'))) {
                if ($k == 0) {
                    $select = 'checked';
                }
            } else {
                if ($value == $INPUT->str('add_stream')) {
                    $select = 'checked';
                }
            }
            $v = "stream[" . $value . "]";
            $l = $value;

            $form->addElement(form_makeCheckboxField($v, 1, $l, '', '', array($select => null)));
        }

        $form->addHidden('newsid', $this->helper->findimax('feeds'));
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('btn_add_news')));
        html_form('addnews', $form);
    }

}

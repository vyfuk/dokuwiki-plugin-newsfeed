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

class admin_plugin_fksnewsfeed extends DokuWiki_Admin_Plugin {

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
        global $imax;
        $this->helper->deletecache();
        $this->helper->findimax();
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
                $this->getpermutnews();
        }
    }

    private function getaddnews() {
        global $lang;
        global $imax;
        echo '<script type="text/javascript" charset="utf-8">'
        . 'var maxfile=' . $imax . ';</script>';

        echo '<h1 class="fkshover" id="fks_news_add">' . $this->getLang('addmenu') . '</h1>';

        echo '<div class="fks_news_add" style="display: none">';
        echo '<span> ' . $this->getLang('addnews') . ' ' . $imax . '</span>';

        $form = new Doku_Form(array('id' => 'addtowiki', 'method' => 'POST', 'class' => 'fksreturn'));
        $form->addHidden("newsdo", "add");
        $form->addHidden('newsid', $imax);
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
        html_form('addnews', $form);
        echo '</div>';
    }

    private function getpermutnews() {
        global $lang;
        global $imax;

        echo '<h1 class="fkshover" id="fks_news_permut" >' . $this->getLang('permutmenu') . '</h1>'
        . '<div class="fks_news_permut" style="display:none;">';
        //warningy
        echo $this->getnewswarning();

        echo '<form method="post" id="fksnewsadminperm" onsubmit="return false" '
        . 'action=doku.php?do=admin&page=fksnewsfeed>'
        . '<input type="hidden" name="maxnews" value="' . $imax . '"></td>'
        . '<input type="hidden" name="newsdo" value="permut"></td>'
        . '<table class="newspermuttable">';


        echo '<thead><tr><th>' . $this->getLang('IDnews') . '</th>
        <th></th>
        <th>' . $this->getLang('newspermold') . '</th>
        <th colspan="2">' . $this->getLang('newspermnew') . '</th>
        <th>' . $this->getLang('newrender') . '</th>
        <th class="fksnewsinfo">' . $this->getLang('newsname') . '</th></tr></thead>';


        for ($i = $imax - 1; $i > 0; $i--) {
            $this->getnewstr($i);
        }

        echo '</table>';
        echo '<input type="submit" onclick="newspermsubmit()" value="' . $this->getLang('newssave') . '" class="button">';
        echo '</form>';


        $form = new Doku_Form(array(
            'id' => 'fks_news_admin_edit_form',
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin&page=fksnewsfeed",
            "onsubmit" => "return false"));
        $form->addHidden("do", "edit");
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement('<input type="hidden" id="fksnewsadmineditvalue" name="id" value=""></form>');
        html_form('addnews', $form);

        echo '</div>';
    }

    private function getnewswarning() {
        global $lang;

        $to_page.= '<div class="error">'
                . '<p><span style="font-weight:bold;font-size:130%">'
                . $this->getLang('permwarning1')
                . '</span></p>'
                . '<p><span >'
                . $this->getLang('permwarning2')
                . '</span></p>'
                . '<p><span >'
                . $this->getLang('permwarning3')
                . '</span></p>'
                . '</div>';
        return $to_page;
    }

    private function returnnewspermut() {
        global $lang;
        global $newsreturndata;
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
            $datawrite[$newsreturndata['permutnew' . $i]] = $newsreturndata['newIDsrender' . $i];
        }
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
            $datawrite['write'].=';' . $datawrite[$i] . ';';
        }
        echo $this->helper->controlData($datawrite["write"]);


        $form = new Doku_Form(array(
            'id' => "addtowiki",
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin&page=fksnewsfeed"
        ));
        $form->addElement(form_makeButton('submit', '', $this->getLang('returntomenu')));
        html_form('addnews', $form);
    }

    private function getnewstr($i) {
        global $lang;
        global $imax;
        $boolrender = false;
        $rendernews = $this->helper->loadnews();
        $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);

        if ($rendernewsbool[1] == 'T') {
            $boolrender = true;
        }

        $newsdata = $this->helper->loadnewssimple($rendernewsbool[0]);
        $newsdata = $this->helper->extractParamtext($newsdata);

        if (strlen($newsdata['name']) > 25) {
            $newsdata['name'] = substr($newsdata['name'], 0, 25) . '...';
        }

        echo '<tr id="fks_news_admin_tr' . $i . '">';
        echo $this->helper->getnewstd("fksnewsid", "fks_news_admin_id" . $i, $rendernewsbool[0]);
        echo $this->helper->getnewstd("fksnewsedit", "fks_news_admin_edit" . $i, ' '
                . '<input class="fksnewsinputedit" type="submit" onclick="newseditsibmit('
                . "'" . $this->helper->getwikinewsurl($rendernewsbool[0]) . "'"
                . ')" value="' . $this->getLang('subeditnews') . '" class="button">');

        echo $this->helper->getnewstd("fksnewspermold", "fks_news_admin_perm_old" . $i, $i);
        echo $this->helper->getnewstd("fksnewspermnew", "fks_news_admin_perm_new" . $i, ' '
                . '<input '
                . 'class="fksnewsinputperm" '
                . $this->helper->fksnewsboolswitch(' '
                        . 'title="' . $this->getLang('notreadonly') . '" ', ' '
                        . 'readonly="readonly" '
                        . 'title="' . $this->getLang('readonly') . '" ', $this->getConf('editnumber'))
                . 'type="text" '
                . 'id="fks_news_admin_permut_new_input' . $i. '" '
                . 'name="permutnew' . $rendernewsbool[0]  . '" '
                . 'value="'.$i. '">');
        echo $this->helper->getnewstd(" ", " ", ' '
                . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/up.gif" class="fks_news_admin_up">'
                . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/down.gif" class="fks_news_admin_down">');
        echo $this->helper->getnewstd(" ", "fks_news_admin_view" . $i, ' '
                . '<select class="fksnwsselectperm" name="newIDsrender' . $i . '">'
                . '<option value="' . $i . '-T" ' . $this->helper->fksnewsboolswitch('selected="selected', '', $boolrender) . '">'
                . $this->getLang('display') . '</option>'
                . '<option value="' . $i . '-F" ' . $this->helper->fksnewsboolswitch('', 'selected="selected"', $boolrender) . '>'
                . $this->getLang('nodisplay') . '</option></select>');
        echo $this->helper->getnewstd("fks_news_info", 'fks_news_admin_info' . $i, ''
                . '<span style="color:' . $this->helper->fksnewsboolswitch('#000', '#999', $boolrender) . '">'
                . $newsdata['name'] . '</span>');
        echo '</tr>';


        echo '<div class="fksnewsmoreinfo" id="fks_news_admin_info' . $i . '_div" style=" " >';

        echo $this->getLang('author') . ': ' . $newsdata['author'] . '<br>';
        echo $this->getLang('email') . ': ' . $newsdata['email'] . '<br>';
        echo $this->getLang('date') . ': ' . $newsdata['newsdate'];
        echo '<div class="fksnewsmoreinfotext">';
        echo p_render("xhtml", p_get_instructions($newsdata["text"]), $info);
        echo '</div>';
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

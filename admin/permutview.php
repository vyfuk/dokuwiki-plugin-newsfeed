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

                $imax = $this->helper->findimax();
                echo '<script type="text/javascript" charset="utf-8">'
                . 'var maxfile=' . $imax . ';</script>';
                /*
                 * add news
                 */
                //$this->getaddnews();
                /*
                 * permutation news
                 */
                $this->getpermutnews();

            //$this->geteditnews();
        }
        echo '<div id="myDiv"> some text </div>'
        . '<form id="load_new"> <input type="text" name="news_id_new">'
        . '<input type="submit" id="MyDivEdit" class="MyDivEdit">'
        . '</form>';
    }

    private function getpermutnews() {
        global $lang;
        $imax = $this->helper->findimax();

        echo '<h1 class="fkshover" id="fks_news_permut" >' . $this->getLang('permutmenu') . '</h1>'
        . '<div class="fks_news_permut">';
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


        for ($i = 0; $i < $imax - 1; $i++) {


            $rendernews = $this->helper->loadnews();
            $rendernewsbool = preg_split('/-/', $rendernews[$i]);

            if ($rendernewsbool[1] == 'T') {
                $boolrender = true;
                $this->getnewstr($i);
            } else {
                continue;
            }
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

        $rendernews = $this->helper->loadnews();
        $rendernewsbool = preg_split('/-/', $rendernews[$i]);


        $newsdata = $this->helper->extractParamtext($this->helper->loadnewssimple($rendernewsbool[0]));

        $newsdata['name'] = $this->helper->shortName($newsdata['name'], 25);
        if (strlen($newsdata['name']) > 25) {
            $newsdata['name'] = substr($newsdata['name'], 0, 22) . '...';
        }

        echo '<tr id="fks_news_admin_tr' . $i . '">';
        echo $this->helper->getnewstd("fksnewsid", "fks_news_i" . $i, $i + 1);


        //echo $this->helper->getnewstd("fksnewspermold", "fks_news_admin_perm_old" . $i, $i);
        echo $this->helper->getnewstd("fksnewspermnew", "fks_news_admin_perm_new" . $i, ' '
                . '<input '
                . 'class="fksnewsinputperm" '
                . $this->helper->fksnewsboolswitch(' '
                        . 'title="' . $this->getLang('notreadonly') . '" ', ' '
                        //. 'readonly="readonly" '
                        . 'title="' . $this->getLang('readonly') . '" ', $this->getConf('editnumber'))
                . 'type="text" '
                . 'id="fks_news_admin_permut_new_input' . $i . '" '
                . 'name="permutnew' . $i . '" '
                . 'value="' . $rendernewsbool[0] . '">');
        echo $this->helper->getnewstd(" ", " ", ' '
                . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/up.gif" class="fks_news_admin_up">'
                . '<img src="' . DOKU_BASE . 'lib/plugins/fksnewsfeed/images/down.gif" class="fks_news_admin_down">');
        echo $this->helper->getnewstd(" ", "fks_news_admin_view" . $i, ' '
                . '<select class="fksnwsselectperm" name="newIDsrender' . $i . '">'
                . '<option value="' . $i . '-T" selected="selected">'
                . $this->getLang('display') . '</option>'
                . '<option value="' . $i . '-F" >'
                . $this->getLang('nodisplay') . '</option></select>');
        echo $this->helper->getnewstd("fks_news_info", 'fks_news_admin_info' . $i, ''
                . '<span style="color:#000' . '">'
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

}

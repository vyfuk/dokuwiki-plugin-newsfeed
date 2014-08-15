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
        global $Rdata;
        $Rdata = $_POST;

        switch ($Rdata['newsdo']) {
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
        echo '<div id="lost_news"> some text </div>'
        . '<form id="load_new" onsubmit="return false"> '
        . '<input type="text" name="news_id_new">'
        . '<input type="submit">'
        . '</form>';
    }

    private function getpermutnews() {
        global $lang;
        $imax = $this->helper->findimax();

        echo '<h1 class="fkshover">' . $this->getLang('permutmenu') . '</h1>'
        . '<div class="fks_news_permut">';
        //warningy
        echo $this->getnewswarning();

        echo '<form method="post" id="fksnewsadminperm" '
        // . 'action=doku.php?do=admin&page=fksnewsfeed
        . '>'
        . '<input type="hidden" name="maxnews" value="' . $imax . '"></td>'
        . '<input type="hidden" name="newsdo" value="permut"></td>'
        . '<table class="newspermuttable">';


        echo '<thead><tr><th>' . $this->getLang('newspermold') . '</th>
              <th>' . $this->getLang('IDnews') . '</th>
                  <th></th>
        <th>' . $this->getLang('newrender') . '</th>
        <th class="fksnewsinfo">' . $this->getLang('newsname') . '</th></tr></thead>';

        $this->getnewstr(0, null);

        $i = 1;
        foreach ($this->helper->loadnews() as $key => $value) {
            list($no) = preg_split('/-/', $value);

            $this->getnewstr($i, $no);
            $i++;
        }

        echo '</table>';
        echo '<input type="submit" value="' . $this->getLang('newssave') . '" class="button">';
        echo '</form>';
    }

    private function getnewstr($i, $no) {
        /** @var je poradie $i */
        /** @var je ID novinky $no  */
        global $lang;

        //$rendernewsbool[0] = $no;
        $newsdata = $this->helper->extractParamtext($this->helper->loadnewssimple($no));

        $newsdata['name'] = $this->helper->shortName($newsdata['name'], 25);

        echo '<tr>';
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
                . 'name="newson' . $i . '" '
                . 'value="' . $no . '">');
        echo $this->helper->getnewstd(" ", " ", ' '
                . '<img src="' . DOKU_INC . 'lib/plugins/fksnewsfeed/images/up.gif" class="fks_news_admin_up">'
                . '<img src="' . DOKU_INC . 'lib/plugins/fksnewsfeed/images/down.gif" class="fks_news_admin_down">');
        echo $this->helper->getnewstd(" ", "fks_news_admin_view" . $i, ' '
                . '<select class="fksnwsselectperm" name="newsonR' . $i . '">'
                . '<option value="T" selected="selected">'
                . $this->getLang('display') . '</option>'
                . '<option value="F" >'
                . $this->getLang('nodisplay') . '</option></select>');
        echo $this->helper->getnewstd("fks_news_info", 'fks_news_admin_info' . $i, ''
                . '<span id="fks_news_admin_info' . $i . '_span" style="color:#000">'
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
        global $Rdata;
        //print_r($Rdata);
        
        
        echo $this->helper->controlData();


        $form = new Doku_Form(array(
            'id' => "addtowiki",
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin&page=fksnewsfeed"
        ));
        $form->addElement(form_makeButton('submit', '', $this->getLang('returntomenu')));
        html_form('addnews', $form);
    }

}

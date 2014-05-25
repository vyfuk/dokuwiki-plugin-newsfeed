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

        deletecache();
        global $imax;
        for ($i = 1; true; $i++) {
            $newsurl = 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt';
            $newsurl = str_replace("@i@", $i, $newsurl);
            if (file_exists($newsurl)) {
                continue;
            } else {
                $imax = $i;
                break;
            }
        }

//echo '<script type="text/javascript" charset="utf-8" src="lib/plugins/fksnewsfeed/script.js"></script>';
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

    private function loadnewssimple($i) {
        global $lang;
        $newsurl = str_replace("@i@", $i, 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt');
        $newsdata = io_readFile($newsurl, false);
        return $newsdata;
    }

    private function getaddnews() {
        global $lang;
        global $imax;
        echo '<script type="text/javascript" charset="utf-8">';
        echo 'var maxfile=' . $imax . '; formax=maxfile+1;var permut=new Array();for (i=1;i<formax;i++){permut[i]=i;};</script>';

        echo '<h1 class="fkshover" onclick="viewnewsadmin(';
        echo "'newsadd'";
        echo ')">' . $this->getLang('addmenu') . '</h1>';

        echo '<div id="newsadd" style="display: none">';
        echo '<span> ' . $this->getLang('addnews') . ' ';
        echo $imax;
        echo '</span>';
        echo '<form method="post" action=doku.php?id=start&do=admin&page=fksnewsfeed>';

        echo '<input type="hidden" name="newsdo" value="add">';
        echo '<input type="hidden" name="newsid" value="' . $imax . '">';
        echo '<input type="submit" value="' . $this->getLang('subaddnews') . '" class="button" title="Pridať novinku [E]">';
        echo '</form>';
        echo '</div>';
    }

    private function getpermutnews() {
        global $lang;
        global $imax;

        echo '<h1 class="fkshover" '
        . getnewsjsfce("onclick", "viewnewsadmin", 'newspermut') .
        '>' . $this->getLang('permutmenu') . '</h1>';
        echo '<div '
        . 'id="newspermut" '
        . 'style="display: none">';
        /*
         * warningy
         */
        echo '<div class="fksnewswarning">'
        . '<p>'
        . '<span '
        . 'class="newswarning">'
        . $this->getLang('permwarning1')
        . '</span></p>';
        echo '<p><span >' . $this->getLang('permwarning2') . '</span></p>';
        echo '<p><span >' . $this->getLang('permwarning3') . '</span></p></div>';

        echo '<form '
        . 'method="post" '
        . 'id="fksnewsadminperm" '
        . 'onsubmit="return false" '
        . 'action=doku.php?do=admin&page=fksnewsfeed>';
        echo '<input '
        . 'type="hidden" '
        . 'name="maxnews" '
        . 'value="' . $imax . '">';
        echo '<input '
        . 'type="hidden" '
        . 'name="newsdo" '
        . 'value="permut">';

        /*
         * begin org table
         */

        echo '<table class="newspermuttable">';

        echo '<thead><tr><th>' . $this->getLang('IDnews') . '</th>';
        echo '<th></th>';
        echo '<th>' . $this->getLang('newspermold') . '</th>';
        echo '<th colspan="2">' . $this->getLang('newspermnew') . '</th>';
//echo '<td></td>';
//echo '<td></td>';
        echo '<th>' . $this->getLang('newrender') . '</th>';
        echo '<th class="fksnewsinfo">' . $this->getLang('newsname') . '</th></tr></thead>';


        for ($i = $imax - 1; $i > 0; $i--) {
            $boolrender = false;
            $rendernews = loadnews();
            $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);

            if ($rendernewsbool[1] == 'T') {
                $boolrender = true;
            }

            $this->newstebletd($rendernewsbool[0], $i, $boolrender);
            $this->newsinfo($i);
        }

        echo '</table>';
        echo '<input type="submit" onclick="newspermsubmit()" value="' . $this->getLang('newssave') . '" class="button">';
        echo '</form>';

        echo '<form id="fksnewsadminedit" onsubmit="return false" method="post" action=doku.php>';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" id="fksnewsadmineditvalue" name="id" value=""></form>';
        echo '</div>';
    }

    private function returnnewspermut() {
        global $lang;
        global $newsreturndata;
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
            $datawrite[$newsreturndata['permutnew' . $i]] = $newsreturndata['newIDsrender' . $i];
        }
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
//echo ';' . $datawrite[$i] . ';';
            $datawrite['write'].=';' . $datawrite[$i] . ';';
        }
        file_put_contents("data/meta/newsfeed.csv", $datawrite['write']);
        echo $this->getLang('autoreturn');
        echo '<form method="post" id="addtowiki" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
        echo '<input type="submit"  value="' . $this->getLang('returntomenu') . '" class="button">';
        echo '</form>';
        echo '<p>Data: <br>' . $datawrite['write'] . '</p>';
    }

    private function newstebletd($i, $renderi, $boolrender) {
        global $lang;
        $newsfeed = preg_split('/====/', $this->loadnewssimple($i));
        if (strlen($newsfeed[1]) > 25) {
            $newsfeed[1] = substr($newsfeed[1], 0, 25) . '...';
        }
        $newsurl = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
        $newsurl = str_replace("@i@", $i, $newsurl);


        echo '<tr id="fksnewsadmintr' . $renderi . '">';
        echo getnewstd('fksnewsadminid' . $renderi, "fksnewsid", $i);

        echo getnewstd('fksnewsadminedit' . $renderi, "fksnewsedit", ' '
                . '<input '
                . 'class="fksnewsinputedit" '
                . 'type="submit" '
                . getnewsjsfce('onclick', "newseditsibmit", $newsurl)
                . 'value="' . $this->getLang('subeditnews') . '" '
                . 'class="button">');

        echo getnewstd('fksnewsadminpermold' . $renderi, "fksnewspermold", $renderi);



        echo getnewstd('fksnewspermnew' . $renderi, "fksnewspermnew", ' '
                . '<input class="fksnewsinputperm" '
                . fksnewsboolswitch(' '
                        . 'title="' . $this->getLang('notreadonly').'" ', ' '
                        . 'readonly="readonly" '
                        . 'title="' . $this->getLang('readonly').'" ', $this->getConf('editnumber'))
               
                . 'type="text" id="fkspermutnew' . $i . '" '
                . 'name="permutnew' . $i . '" '
                . 'value="' . $renderi . '">');

        echo getnewstd("fksnewsbutton", "fksnewsbutton", ' '
                . '<img src="' . wl() . '/../lib/plugins/fksnewsfeed/images/up.gif" '
                . getnewsjsfce("onclick", "newsvalueup", $renderi)
                . '>'
                . '<img src="' . wl() . '/../lib/plugins/fksnewsfeed/images/down.gif" '
                . getnewsjsfce("onclick", "newsvaluedown", $renderi)
                . '>');

        echo getnewstd('fksnewsadminview' . $renderi, "fksnewsadminview", ' ' .
                '<select ' .
                'class="fksnwsselectperm" ' .
                'name="newIDsrender' . $i . '">' .
                '<option value="' . $i . '-T" ' . fksnewsboolswitch('selected="selected', '', $boolrender) . '">' . $this->getLang('display') . '</option>' .
                '<option value="' . $i . '-F" ' . fksnewsboolswitch('', 'selected="selected"', $boolrender) . '>' . $this->getLang('nodisplay') . '</option>' .
                '</select>');
        echo getnewstd('fksnewsadmininfo' . $renderi, 'fksnewsinfo', ' ' .
                '<span ' .
                getnewsjsfce("onMouseOver", "newsviewmore", $i) .
                getnewsjsfce("onMouseOut", "newsviewmoredef", $i) .
                ' style="color:' . fksnewsboolswitch('#000', '#999', $boolrender) . '">' . $newsfeed[1] . '</span>');
        echo '</tr>';
    }

    private function newsinfo($i) {
        global $lang;
        $newsdata = $this->loadnewssimple($i);
        $newsdate = preg_split('/newsdate/', $newsdata);
        $newsauthor = preg_split('/newsauthor/', $newsdata);
        $newsauthorinfo = preg_split('/\|/', substr($newsauthor[1], 3, -4));
        $newsfeed = preg_split('/====/', $newsdata);

        echo '<div class="fksnewsmoreinfo" id="fksnewsmoreinfo' . $i . '" style="display:none;position:absolute;">';
        echo $this->getLang('author') . ': ' . $newsauthorinfo[1] . '<br>';
        echo $this->getLang('email') . ': ' . $newsauthorinfo[0] . '<br>';
        echo $this->getLang('date') . ': ' . substr($newsdate[1], 1, -2);
        echo '<div style="background-color: #f0f0f0; border-radius: 5px; width: 100%">';
        echo p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
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

        $fksnews.="<newsdate>@DATE@</newsdate>\n"
                . "<newsauthor>[[@MAIL@|@NAME@]]</newsauthor>"
                . "\n"
                . "==== Název aktuality ==== \n"
                . "Tady napiš text aktuality.\n"
                . "\n";

        //$sig = $conf['fksnewsfeeds'];
        //$sig = dformat(null,$sig);
        $fksnews = str_replace('@USER@', $_SERVER['REMOTE_USER'], $fksnews);
        $fksnews = str_replace('@NAME@', $INFO['userinfo']['name'], $fksnews);
        $fksnews = str_replace('@MAIL@', $INFO['userinfo']['mail'], $fksnews);
        $fksnews = str_replace('@DATE@', dformat(), $fksnews);
        file_put_contents(str_replace("@i@", $newsreturndata['newsid'], 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt'), $fksnews);

        echo '<form method="post" id="addtowiki" action=doku.php>';
        echo '<div class="" >';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        $newsurlnew = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
        $newsurlnew = str_replace("@i@", $newsreturndata['newsid'], $newsurlnew);
        echo '<input type="hidden" name="id" value="' . $newsurlnew . '">';
        echo '<input type="submit" value="' . $this->getLang('subaddwikinews') . '" class="button" title="Přidat novinku [E]">';
        echo '</div>';
        echo '</form>';
    }

}

function getnewstd($id, $class, $text) {
    $td = '<td id="' . $id . '" class="' . $class . '" >' . $text . '</td>';
    return $td;
}

function getnewsjsfce($act, $fce, $elem) {
    $js = $act . '="' . $fce . "('" . $elem . "')" . '"';
    return $js;
}

function deletecache() {
    $files = glob('data/cache/*/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    return;
}

function fksnewsboolswitch($color1, $color2, $bool) {
    if ($bool) {
        return $color1;
    } else {
        return $color2;
    }
}

function loadnews() {
    return preg_split('/;;/', substr(io_readFile("data/meta/newsfeed.csv", FALSE), 1, -2));
}

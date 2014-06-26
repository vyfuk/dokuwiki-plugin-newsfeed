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

        echo '<div > <h1 class="fkshover" id="fks_news_add">' . $this->getLang('addmenu') . '</h1></div>';

        echo '<div class="fks_news_add" style="display: none">';
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

        echo '<h1 class="fkshover" id="fks_news_permut" >' . $this->getLang('permutmenu') . '</h1>';
        echo '<div class="fks_news_permut" style="display:none;">';
        //warningy
        echo $this->getnewswarning();

        echo '<form method="post" id="fksnewsadminperm" onsubmit="return false" action=doku.php?do=admin&page=fksnewsfeed>';
        echo '<input type="hidden" name="maxnews" value="' . $imax . '"></td>';
        echo '<input type="hidden" name="newsdo" value="permut"></td>';
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
            $this->getnewstr($i);
        }

        echo '</table>';
        echo '<input type="submit" onclick="newspermsubmit()" value="' . $this->getLang('newssave') . '" class="button">';
        echo '</form>';

        echo '<form id="fksnewsadminedit" onsubmit="return false" method="post" action=doku.php>';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" name="target" value="plugin_fksnewsfeed">';
        echo '<input type="hidden" id="fksnewsadmineditvalue" name="id" value=""></form>';
        echo '</div>';
    }

    private function getnewswarning() {
        global $lang;

        $to_page.= '<div class="fksnewswarning"><p><span style="font-weight:bold;font-size:130%">' . $this->getLang('permwarning1') . '</span></p>';
        $to_page.='<p><span >' . $this->getLang('permwarning2') . '</span></p>';
        $to_page.= '<p><span >' . $this->getLang('permwarning3') . '</span></p></div>';
        return $to_page;
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
        echo '<form method="post" id="addtowiki" action=' . DOKU_BASE . '?do=admin&page=fksnewsfeed>';
        echo '<input type="submit"  value="' . $this->getLang('returntomenu') . '" class="button">';
        echo '</form>';
        echo '<p>Data: <br>' . $datawrite['write'] . '</p>';
    }

    private function getnewstr($i) {
        global $lang;
        global $imax;
        $boolrender = false;
        $rendernews = loadnews();
        $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);

        if ($rendernewsbool[1] == 'T') {
            $boolrender = true;
        }

        $newsurl = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
        $newsurl = str_replace("@i@", $i, $newsurl);
        
        $newsfeed = preg_split('/====/', $this->loadnewssimple($i));
        if (strlen($newsfeed[1]) > 25) {
            $newsfeed[1] = substr($newsfeed[1], 0, 25) . '...';
        }

        $newsdata = $this->loadnewssimple($i);
        $newsdate = preg_split('/newsdate/', $newsdata);
        $newsauthor = preg_split('/newsauthor/', $newsdata);

        echo '<tr id="fksnewsadmintr' . $i . '">';
        echo getnewstd("fksnewsid", "fksnewsadminid" . $i, $i);
        echo getnewstd("fksnewsedit", "fksnewsadminedit" . $i, ' '
                . '<input class="fksnewsinputedit" type="submit" onclick="newseditsibmit('
                . "'" . $newsurl . "'"
                . ')" value="' . $this->getLang('subeditnews') . '" class="button">');

        echo getnewstd("fksnewspermold", "fksnewsadminpermold" . $i, $rendernewsbool[0]);
        echo getnewstd("fksnewspermnew", "fksnewspermnew" . $i, ' '
                . '<input '
                . 'class="fksnewsinputperm" '
                . 'readonly="readonly" '
                . 'type="text" '
                . 'id="fkspermutnew' . $i . '" '
                . 'name="permutnew' . $i . '" '
                . 'value="' . $rendernewsbool[0] . '">');
        echo getnewstd(" ", " ", '<img src="lib/plugins/fksnewsfeed/images/up.gif" onclick=newsvalueup('
                . "'" . $i . "'"
                . ')><img src="lib/plugins/fksnewsfeed/images/down.gif" onclick=newsvaluedown('
                . "'" . $i . "'"
                . ')>');
        echo getnewstd(" ", "fksnewsadminview" . $i, ' '
                . '<select class="fksnwsselectperm" name="newIDsrender' . $i . '">'
                . '<option value="' . $i . '-T" ' . fksnewsboolswitch('selected="selected', '', $boolrender) . '">'
                . $this->getLang('display') . '</option>'
                . '<option value="' . $i . '-F" ' . fksnewsboolswitch('', 'selected="selected"', $boolrender) . '>'
                . $this->getLang('nodisplay') . '</option></select>');

        



        echo '<td id="'. $i . '" class="fks_news_info" ><span ';
        /*echo 'onMouseOver="newsviewmore(';
        echo "'" . $i . "'";
        echo ')" onMouseOut="newsviewmoredef(';
        echo "'" . $i . "'";
        echo ')" ';*/
        echo 'style="color:' . fksnewsboolswitch('#000', '#999', $boolrender) . '">' . $newsfeed[1] . '</span></td></tr>';


        echo '<div class="fksnewsmoreinfo" id="fksnewsmoreinfo' . $i . '" style="display:none;position:absolute;">';
        $newsauthorinfo = preg_split('/\|/', substr($newsauthor[1], 3, -4));
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

function getnewstd($class, $id, $text) {
    $td = '<td class="' . $class . '" id="' . $id . '"> ' . $text . '</td>';
    return $td;
}

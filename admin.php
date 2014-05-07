<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

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
                 * edit news
                 */
                $this->geteditnews();
                /*
                 * delete news
                 */
                $this->getdeletenews();
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
        echo 'var maxfile=' . $imax . ';</script>';

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

    private function geteditnews() {
        global $lang;
        global $imax;
        echo '<h1 class="fkshover" onclick="viewnewsadmin(';
        echo "'newsedit'";
        echo ')">' . $this->getLang('editmenu') . '</h1>';
        echo '<div id="newsedit" style="display: none">';
        echo '<form method="post" action=doku.php>';

        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
//echo $imax;
        for ($i = $imax - 1; $i > 0; $i--) {

            $newsdata = $this->loadnewssimple($i);
            $newsfeed = preg_split('/====/', $newsdata);
            $newsdate = preg_split('/newsdate/', $newsdata);
            $newsauthor = preg_split('/newsauthor/', $newsdata);

            echo '<div id="" style="border-bottom: 1px solid #dcdcdc">';
            $newsurl = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
            $newsurl = str_replace("@i@", $i, $newsurl);
            echo '<input type="radio" name="id" value="' . $newsurl . '"';
            if ($i == $imax - 1) {
                echo ' checked="checked"';
            } else {
                
            }
            echo '>';
            echo $newsfeed[1];
            echo '<span style="color:#ff4800;cursor:pointer" onclick="viewsedit(';
            echo "'" . $i . "'";
            echo ')">' . $this->getLang('viewmore') . '</span><br>';
            echo '<div id="newsedit' . $i . '" style="display:none">';
            $newsauthorinfo = preg_split('/\|/', substr($newsauthor[1], 3, -4));
            echo $this->getLang('author') . ': ' . $newsauthorinfo[1] . '<br>';
            echo $this->getLang('email') . ': ' . $newsauthorinfo[0] . '<br>';
            echo $this->getLang('date') . ': ' . substr($newsdate[1], 1, -2);
            echo '<div style="background-color: #f0f0f0; border-radius: 5px; width: 100%; padding: 5px 10px">';
            echo p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo ' <input type="submit" value="' . $this->getLang('subeditnews') . '" class="button" title="Upravit novinku [E]">';
        echo '</form>';
        echo '</div>';
    }

    private function getdeletenews() {
        global $lang;
        global $imax;
        echo '<h1 class="fkshover" onclick="viewnewsadmin(';
        echo "'newsdelete'";
        echo ')">' . $this->getLang('deletemenu') . '</h1>';
        echo '<div id="newsdelete" style="display: none">';
        echo $this->getLang('newsviewnow') . ':<br>';
        $rendernews = loadnews();
        for ($i = $imax - 1; $i > 0; $i--) {
            $boolrender = false;
            //echo $rendernews[$imax-1-$i];
            $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);
            //echo $rendernewsbool[0] .' // '. $rendernewsbool[1];
            if ($rendernewsbool[1] == 'T') {
                $boolrender = true;
            }
            echo '<form method="post" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
            echo '<input type="hidden" name="newsdo" value="delete">';
            echo '<input type="hidden" name="IDtodel" value="' . $i . '">';
            if ($boolrender) {
                echo '<input type="hidden" name="typetodel" value="true">';

                echo '<input class="button" type="submit" value="' . $this->getLang('deletenews') . '"> </form>';
            } else {
                echo '<input type="hidden" name="typetodel" value="false">';
                echo '<input class="button" type="submit" value="' . $this->getLang('reviewnews') . '"></form> ';
            }
            echo $this->getLang('newsname') . ': ';
            $newsfeed = preg_split('/====/', $this->loadnewssimple($i));
            echo '<span style="color:' . fksnewsboolswitch('#000', '#cccccc', $boolrender) . '">' . $newsfeed[1] . '</span><br>';
        }
        echo '</div>';
    }

    private function getpermutnews() {
        global $lang;
        global $imax;

        echo '<h1 class="fkshover" onclick="viewnewsadmin(';
        echo "'newspermut'";
        echo ')">' . $this->getLang('permutmenu') . '</h1>';
        echo '<div id="newspermut" style="display: none">';
        echo '<span style="font-weight:bold;color:red;">' . $this->getLang('permwarning') . '</span>';
        echo '<form method="post" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
        echo '<input type="hidden" name="maxnews" value="' . $imax . '"></td>';
        echo '<input type="hidden" name="newsdo" value="permut"></td>';
        echo '<table class="newspermuttable">';
        echo '<tr><td>' . $this->getLang('IDnews') . '</td>';
        
        echo '<td>' . $this->getLang('newspermold') . '</td>';
        echo '<td>' . $this->getLang('newspermnew') . '</td>';
        echo '<td>' . $this->getLang('newrender') . '</td>';
        echo '<td>' . $this->getLang('newsname') . '</tr>';


        for ($i = $imax - 1; $i > 0; $i--) {
            $boolrender = false;
            $rendernews = loadnews();
            $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);

            if ($rendernewsbool[1] == 'T') {
                $boolrender = true;
            }
            echo '<tr><td class="fksnewsid">' . $i . '</td>';
            echo '<td class="fksnewspermold">' . $rendernewsbool[0] . '</td>';
            echo '<td class="fksnewspermnew"><input class="fksnewsinputperm" type="text" name="permutnew' . $i . '" value="' . $rendernewsbool[0] . '">';
            
            echo '<td><select class="fksnwsselectperm" name="newIDsrender' . $i . '">';
            echo '<option value="' . $i . '-T" ' . fksnewsboolswitch('selected="selected', '', $boolrender) . '">Zobraziť</option>';
            echo '<option value="' . $i . '-F" ' . fksnewsboolswitch('', 'selected="selected"', $boolrender) . '>Nezobrazovať</option>';
            echo '</select></td>';
            
            $newsfeed = preg_split('/====/', $this->loadnewssimple($i));
            if (strlen($newsfeed[1]) > 25) {
                $newsfeed[1] = substr($newsfeed[1], 0, 25) . '...';
            }
            echo '<td><span style="color:' . fksnewsboolswitch('#000', '#999', $boolrender) . '">' . $newsfeed[1] . '</span></td></tr>';
        }
        echo '</table>';
        echo '<input type="submit" value="' . $this->getLang('subaddnews') . '" class="button" title="Pridať novinku [E]">';
        echo '</form>';
        echo '</div>';
    }

    private function returnnewspermut() {
        global $lang;
        global $newsreturndata;
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
            $datawrite[$newsreturndata['permutnew' . $i]] = $newsreturndata['newIDsrender' . $i];
        }
        for ($i = $newsreturndata['maxnews'] - 1; $i > 0; $i--) {
            echo ';' . $datawrite[$i] . ';';
            $datawrite['write'].=';' . $datawrite[$i] . ';';
        }
        file_put_contents("data/meta/newsfeed.csv", $datawrite['write']);
        echo '<form method="post" id="addtowiki" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
        echo '<input type="submit"  value="' . $this->getLang('returntomenu') . '" class="button">';
        echo '</form>';
    }

    private function returnnewsadd() {
        global $imax;
        global $newsreturndata;
        global $lang;

        $newsID = io_readFile("data/meta/newsfeed.csv", FALSE);
        $newsID = ';' . $newsreturndata['newsid'] . "-T;" . $newsID;
        file_put_contents("data/meta/newsfeed.csv", $newsID);
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

    private function returnnewsdelete() {
        global $imax;
        global $newsreturndata;
        global $lang;
        echo $this->getLang('autoreturn') . '<br>';
        if ($newsreturndata['typetodel'] == "true") {
            $rendernews = str_replace($newsreturndata['IDtodel'] . '-T;', $newsreturndata['IDtodel'] . '-F;', io_readFile("data/meta/newsfeed.csv", FALSE));
            echo '<span> ' . $this->getLang('nonews') . ' ' . $newsreturndata['IDtodel'] . ' ' . $this->getLang('newsviewfalse') . '</span>';
        } else {
            $rendernews = str_replace($newsreturndata['IDtodel'] . '-F;', $newsreturndata['IDtodel'] . '-T;', io_readFile("data/meta/newsfeed.csv", FALSE));
            echo '<span> ' . $this->getLang('nonews') . ' ' . $newsreturndata['IDtodel'] . ' ' . $this->getLang('newsviewtrue') . '</span>';
        }
        echo '<br><form method="post" id="addtowiki" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
        echo '<input type="submit"  value="' . $this->getLang('returntomenu') . '" class="button">';
        echo '</form>';
        file_put_contents("data/meta/newsfeed.csv", $rendernews);
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

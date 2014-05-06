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
        $newsdo = $_POST['newsdo'];
        switch ($newsdo) {
            case "add":
                $this->returnnewsadd();
                break;


            case "delete":
                $this->returnnewsdelete();

                break;
            case "permut":
                break;
            default:
                $this->getaddnews();
                $this->geteditnews();
                $this->getdeletenews();






                /*
                 * edit news
                 */


                /*
                 * delete news
                 */


                /*
                 * permutation news
                 */

                echo '<h1 class="fkshover" onclick="viewnewsadmin(';
                echo "'permutmenu'";
                echo ')">' . $this->getLang('permutmenu') . '</h1>';
                echo '<span style="">' . $this->getLang('permutmenu') . '</span>';
                 echo '<table>';
                 echo '<tr><td>File NO.</td><td>Now rennder NO</td><td>Edit now NO</td><td>Text</tr>';
                echo '<form method="post" action=doku.php?id=start&do=admin&page=fksnewsfeed>';
               
                for ($i = $imax - 1; $i > 0; $i--) {
                    $boolrender = false;
                    $rendernews = loadnews();
                    $rendernewsbool = preg_split('/-/', $rendernews[$imax - 1 - $i]);

                    if ($rendernewsbool[1] == 'T') {
                        $boolrender = true;
                    }
                    echo '<tr><td>' . $i . '</td>';
                    echo '<td>' . $rendernewsbool[0] . '</td>';
                    echo '<td><input type="text" name="IDtodel' . $i . '" value="' . $rendernewsbool[0] . '">';
                    echo '<input type="hidden" name="IDtodel" value="' . $i . '"></td>';
                    $newsfeed = preg_split('/====/', $this->loadnewssimple($i));
                    echo '<td><span style="color:' . getnewsColor('#000', '#cccccc', $boolrender) . '">' . $newsfeed[1] . '</span></td></tr>';
                };
                echo '</form>';
                echo '</table>';
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
            echo '<span style="color:' . getnewsColor('#000', '#cccccc', $boolrender) . '">' . $newsfeed[1] . '</span><br>';
        };
        echo '</div>';
    }

    private function returnnewsadd() {
        global $imax;
        global $lang;
        echo $_POST['newsdo'];
        $newsID = io_readFile("data/meta/newsfeed.csv", FALSE);
        $newsID = ';' . $_POST['newsid'] . "-T;" . $newsID;
        file_put_contents("data/meta/newsfeed.csv", $newsID);
        echo '<form method="post" id="addtowiki" action=doku.php>';
        echo '<div class="" >';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        $newsurlnew = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
        $newsurlnew = str_replace("@i@", $_POST['newsid'], $newsurlnew);
        echo '<input type="hidden" name="id" value="' . $newsurlnew . '">';
        echo '<input type="submit" value="' . $this->getLang('subaddwikinews') . '" class="button" title="Přidat novinku [E]">';
        echo '</div>';
        echo '</form>';
    }

    private function returnnewsdelete() {
        global $imax;
        global $lang;
        echo $this->getLang('autoreturn') . '<br>';
        if ($_POST['typetodel'] == "true") {
            $rendernews = str_replace($_POST['IDtodel'] . '-T;', $_POST['IDtodel'] . '-F;', io_readFile("data/meta/newsfeed.csv", FALSE));
            echo '<span> ' . $this->getLang('nonews') . ' ' . $_POST['IDtodel'] . ' ' . $this->getLang('newsviewfalse') . '</span>';
        } else {
            $rendernews = str_replace($_POST['IDtodel'] . '-F;', $_POST['IDtodel'] . '-T;', io_readFile("data/meta/newsfeed.csv", FALSE));
            echo '<span> ' . $this->getLang('nonews') . ' ' . $_POST['IDtodel'] . ' ' . $this->getLang('newsviewtrue') . '</span>';
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
        if (is_file($file))
            unlink($file);
    }
    return;
}

function getnewsColor($color1, $color2, $bool) {
    if ($bool) {
        return $color1;
    } else {
        return $color2;
    }
}

function loadnews() {
    return preg_split('/;;/', substr(io_readFile("data/meta/newsfeed.csv", FALSE), 1, -2));
}

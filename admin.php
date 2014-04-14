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

    function getMenuSort() {
        return 229;
    }

    function forAdminOnly() {
        return false;
    }

    function getMenuText($language) {
        $menutext = $this->getLang('menu');
        return $menutext;
    }

    function handle() {
        global $lang;
    }

    function html() {
        $imax;
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


        echo '<script type="text/javascript" charset="utf-8" src="lib/plugins/fksnewsfeed/script.js"></script>';
        echo '<script type="text/javascript" charset="utf-8">';
        echo 'var maxfile=' . $imax . ';</script>';

        echo '<h1 style="cursor:pointer" onclick="viewnewsadmin(';
        echo "'newsadd'";
        echo ')">' . $this->getLang('addmenu') . '</h1>';
        echo '<div id="newsadd" style="display: none">';
        echo '<span> ' . $this->getLang('addnews') . ' ';
        echo $imax;
        echo '</span>';
        echo '<form method="post" action=doku.php>';
        echo '<div class="" >';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        $newsurlnew = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
        $newsurlnew = str_replace("@i@", $imax, $newsurlnew);
        echo '<input type="hidden" name="id" value="' . $newsurlnew . '">';
        echo ' <input type="submit" value="' . $this->getLang('subaddnews') . '" class="button" title="Pridať novinku [E]">';
        echo '</div>';
        echo '</div>';
        echo '</form>';

        /*
         * edit news
         */
        echo '<h1 style="cursor:pointer" onclick="viewnewsadmin(';
        echo "'newsedit'";
        echo ')">' . $this->getLang('editmenu') . '</h1>';
        echo '<div id="newsedit" style="display: none">';
        echo '<form method="post" action=doku.php>';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        //echo $imax;
        for ($i = $imax - 1; $i > 0; $i--) {
            $newsurl = 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt';
            $newsurl = str_replace("@i@", $i, $newsurl);
            $newsdata = io_readFile($newsurl, false);
            $newsfeed = preg_split('/====/', $newsdata);
            $newsdate = preg_split('/newsdate/', $newsdata);
            $newsauthor = preg_split('/newsauthor/', $newsdata);

            echo '<div id="" style="border-bottom: 1px solid #dcdcdc">';
            $newsurl = $this->getConf('newsfolder') . ':' . $this->getConf('newsfile');
            $newsurl= str_replace("@i@", $i, $newsurl);
            echo '<input type="radio" name="id" value="' . $newsurl . '"';
            if($i==$imax - 1){echo ' checked="checked"';}else{}
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

}

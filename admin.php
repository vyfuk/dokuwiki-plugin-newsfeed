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

class admin_plugin_fksnewsfeeds extends DokuWiki_Admin_Plugin {

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
            if (file_exists("data/pages/fksnewsfeeds/news$i.txt")) {
                continue;
            } else {
                $imax = $i - 1;
                break;
            }
        }


        echo '<script type="text/javascript" charset="utf-8" src="lib/plugins/fksnewsfeeds/script.js"></script>';
        echo '<script type="text/javascript" charset="utf-8">';
        echo 'var maxfile='.$i.';</script>';

        echo '<h1 style="cursor:pointer" onclick="viewnewsadmin(';
        echo "'newsadd'";
        echo ')">' . $this->getLang('addmenu') . '</h1>';
        echo '<div id="newsadd" style="display: none">';
        echo '<span> ' . $this->getLang('addnews') . ' ';
        echo $imax + 1;
        echo '</span>';
        echo '<form method="post" action=doku.php>';
        echo '<div class="" >';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" name="id" value="fksnewsfeeds:news';
        echo $imax + 1;
        echo '">';
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
        for ($i = $imax; $i > 0; $i--) {
            $newsfeed = preg_split('/====/', io_readFile("data/pages/fksnewsfeeds/news" . $i . ".txt", false));
            $newsdate = preg_split('/newsdate/', io_readFile("data/pages/fksnewsfeeds/news" . $i . ".txt", false));
            $newsauthor = preg_split('/newsauthor/', io_readFile("data/pages/fksnewsfeeds/news" . $i . ".txt", false));

            echo '<div id="" style="border-bottom: 1px solid #dcdcdc">';
            echo '<input type="radio" name="id" value="fksnewsfeeds:news' . $i . '">';
            //echo '<div >';
            echo $newsfeed[1];
            echo '<span style="color:#ff4800;cursor:pointer" onclick="viewsedit(';
            echo "'".$i."'";
            echo ')">Zobraz podrobnosti</span><br>';
            echo '<div id="newsedit'.$i.'" style="display:none">';
            $newsauthorinfo=preg_split('/\|/',substr($newsauthor[1], 3, -4));
            echo 'author: '.$newsauthorinfo[1]. '<br>email: '. $newsauthorinfo[0].'<br>';
            echo 'datum: ' .substr($newsdate[1], 1, -2);  
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

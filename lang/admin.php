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
            if (file_exists("data/pages/fksnewsfeed/news$i.txt")) {
                continue;
            } else {
                $imax = $i - 1;
                break;
            }
        }


        echo '<script type="text/javascript" charset="utf-8" src="lib/plugins/fksnewsfeed/script.js"></script>';
       
        echo '<h1 onclick="viewnewsadmin(';
        echo "'newsadd'";
        echo ')">' . $this->getLang('addmenu') . '</h1>';
        echo '<div id="newsadd" style="display: none">';
        echo '<span> ' . $this->getLang('addnews') . ' ';
        echo $imax+1;
        echo '</span>';
        echo '<form method="post" action=doku.php>';
        echo '<div class="" >';
        echo '<input type="hidden" name="do" value="edit">';
        echo '<input type="hidden" name="rev" value="0"> ';
        echo '<input type="hidden" name="id" value="fksnewsfeed:news';
        echo $imax+1;
        echo '">';
        echo ' <input type="submit" value="' . $this->getLang('subaddnews') . '" class="button" title="Pridať novinku [E]">';
        echo '</div>';
        echo '</div>';
        echo '</form>';
        
        /*
         * edit news
         */
        echo '<h1 onclick="viewnewsadmin(';
        echo "'newsedit'";
        echo ')">' . $this->getLang('editmenu') . '</h1>';
        echo '<div id="newsedit" style="display: none">';
        echo '<form method="post" action=doku.php>';
        echo '<input type="hidden" name="do" value="edit" />';
        echo '<input type="hidden" name="rev" value="0" /> ';
        echo $imax;
        for ($i = $imax; $i > 0; $i--) {
            $feedsdata = io_readFile("data/pages/fksnewsfeed/news" . $i . ".txt", false);
            //$feedsdata = fksnewsfeed($feedsdata, $match);
            $eqnum = 2;
            $jmax = strlen($feedsdata);
            $j = 0;
            for (; $j < $jmax - 3; $j++) {
                $datapar = substr($feedsdata, $j, 4);
                if ($datapar == '====') {
                    $eqnum--;
                }
                if (!$eqnum) {
                    break;
                }
            }
            $feedsdata = substr($feedsdata, 0, $j + 4);
            $feedsdata = str_replace('====', '', $feedsdata);
            echo '<div class="" >';
            echo '<input type="radio" name="id" value="fksnewsfeed:news' . $i . '">';
            echo '<div >';
            echo p_render("xhtml", p_get_instructions($feedsdata), $info);
            echo '</div>';
            echo '</div>';
        }
        echo ' <input type="submit" value="' . $this->getLang('subeditnews') . '" class="button" title="Upravit novinku [E]">';
        echo '</form>';
        echo '</div>';
    }

}

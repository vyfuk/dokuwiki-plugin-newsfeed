<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

if (!defined('DOKU_LF')) {
    define('DOKU_LF', "\n");
}
if (!defined('DOKU_TAB')) {
    define('DOKU_TAB', "\t");
}

class helper_plugin_fksnewsfeed extends DokuWiki_Plugin {

    function getnewsurl($newsno, $newsurl) {
        $newsurl = str_replace("@i@", $newsno, $newsurl);
        //if (file_exists($newsurl)) {
        return $newsurl;
    }

    function rendernews($match, $newsurl) {
        if (file_exists($newsurl)) {
            $match--;
            if ($match % 2) {
                /*
                 * find even and odd news, css is not same.
                 */
                $to_page.="<div class='fksnewseven'>";
            } else {
                $to_page.="<div class='fksnewsodd'>";
            }
            /*
             * find news autor title and date news and render then
             */
            $newsdata = io_readFile($newsurl, false);
            $newsfeed = preg_split('/====/', $newsdata);
            $newsdate = preg_split('/newsdate/', $newsdata);
            $newsauthor = preg_split('/newsauthor/', $newsdata);

            $to_page.= p_render("xhtml", p_get_instructions('<newsdate>' . substr($newsdate[1], 1, -2) . '-render</newsdate>'), $info);

            $to_page.='<div class="fksnewsheadline">';
            $to_page.= p_render("xhtml", p_get_instructions('===' . $newsfeed[1] . '==='), $info);
            $to_page.="</div>";

            $to_page.='<div class="fksnewsarticle">';
            $to_page.= p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
            $to_page.="</div>";
            $to_page.= p_render("xhtml", p_get_instructions('<newsauthor>' . substr($newsauthor[1], 1, -2) . '-render</newsauthor>'), $info);
            $to_page.='<div class="clearer"></div>';
            $to_page.="</div>";
            $to_page.='<div class="clearer"></div>';
            return $to_page;
        }
    }

}

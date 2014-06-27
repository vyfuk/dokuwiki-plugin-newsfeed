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

    function getnewsurl($newsno) {
        global $lang;
        global $conf;
        $url = str_replace("@i@", $newsno, 'data/pages/'
                . $this->getConf('newsfolder') . '/'
                . $this->getConf('newsfile') . '.txt');
        return $url;
    }

    function getwikinewsurl($i) {
        global $lang;
        global $conf;
        $url = str_replace("@i@", $i, $this->getConf('newsfolder') . ':' . $this->getConf('newsfile'));
        return $url;
    }

    function extractParam($text) {
        global $INFO;
        global $TEXT;
        $param = $this->extractParamtext($TEXT);
        $TEXT = $param["text"];
        unset($param["text"]);
        return $param;
    }

    function extractParamtext($text) {
        global $INFO;
        global $TEXT;


        $newsfeed = preg_split('/====/', $text);
        $newsdate = preg_split('/newsdate/', $text);
        $newsdate = substr($newsdate[1], 1, -2);
        $newsauthor = preg_split('/newsauthor/', $text);
        $newsauthorinfo = preg_split('/\|/', substr($newsauthor[1], 3, -4));
        $param = array(
            'name' => $newsfeed[1],
            'author' => $newsauthorinfo[1],
            'email' => $newsauthorinfo[0],
            'newsdate' => $newsdate,
            'text' => $newsfeed [2]
        );
        return $param;
    }

    /*
     * save a new file with value od USer
     */

    function saveNewNews($newsreturndata) {
        global $INFO;
        $fksnews.="<newsdate>@DATE@</newsdate>\n"
                . "<newsauthor>[[@MAIL@|@NAME@]]</newsauthor>"
                . "\n"
                . "==== Název aktuality ==== \n"
                . "Tady napiš text aktuality.\n"
                . "\n";
        $fksnews = str_replace('@USER@', $_SERVER['REMOTE_USER'], $fksnews);
        $fksnews = str_replace('@NAME@', $INFO['userinfo']['name'], $fksnews);
        $fksnews = str_replace('@MAIL@', $INFO['userinfo']['mail'], $fksnews);
        $fksnews = str_replace('@DATE@', dformat(), $fksnews);
        file_put_contents($this->getnewsurl($newsreturndata['newsid']), $fksnews);
    }

    /*
     * delete casche if is run
     */

    function deletecache() {
        global $conf;
        $files = glob('data/cache/*/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return;
    }

    /*
     * get one <td> with params
     */

    function getnewstd($class, $id, $text) {
        global $conf;
        $td = '<td class="' . $class . '" id="' . $id . '"> ' . $text . '</td>';
        return $td;
    }

    /*
     * 
     */

    function loadnews() {
        return preg_split('/;;/', substr(io_readFile("data/meta/newsfeed.csv", FALSE), 1, -2));
    }

    function loadnewssimple($i) {
        global $lang;
        global $conf;
        $newsurl = $this->getnewsurl($i);
        $newsdata = io_readFile($newsurl, false);
        return $newsdata;
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
            $newsdata = $this->helper->extractParam($newsdata);


            $to_page.= p_render("xhtml", p_get_instructions('<newsdate>' . $newsdata[3] . '-render</newsdate>'), $info);

            $to_page.='<div class="fksnewsheadline">';
            $to_page.= p_render("xhtml", p_get_instructions('===' . $newsdata[0] . '==='), $info);
            $to_page.="</div>";

            $to_page.='<div class="fksnewsarticle">';
            $to_page.= p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
            $to_page.="</div>";
            $to_page.= p_render("xhtml", p_get_instructions('<newsauthor>[[' . $newsdata[2] . '|' . $newsdata[1] . ']]-render</newsauthor>'), $info);
            $to_page.='<div class="clearer"></div>';
            $to_page.="</div>";
            $to_page.='<div class="clearer"></div>';
            return $to_page;
        }
    }

    public function getNewsFile($news) {
        $id = $this->getPluginName() . ":$news";
        return metaFN($id, '.txt');
    }

}

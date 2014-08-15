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

        global $conf;
        $url = str_replace("@i@", $newsno, DOKU_INC . 'data/pages/'
                . $this->getConf('newsfolder') . '/'
                . $this->getConf('newsfile') . '.txt');
        return $url;
    }

    /*
     * get wiki URL with :
     */

    function getwikinewsurl($i) {
        global $lang;
        global $conf;
        $url = str_replace("@i@", $i, $this->getConf('newsfolder') . ':' . $this->getConf('newsfile'));
        return $url;
    }

    /*
     * changed doku text and extract param
     */

    function extractParam($text) {
        global $INFO;
        global $TEXT;
        $param = $this->extractParamtext($text);
        $TEXT = $param["text"];
        unset($param["text"]);
        return $param;
    }

    /*
     * extract param from text
     */

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
        $Wnews = file_put_contents($this->getnewsurl($newsreturndata['newsid']), $fksnews);
        return $Wnews;
    }

    /*
     * delete casche if is run
     */

    function deletecache() {
        global $conf;
        $files = glob(DOKU_INC . 'data/cache/*/*');
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
     * load file with configuration
     */

    function loadnews() {
        return preg_split('/;;/', substr(io_readFile("data/meta/newsfeed.csv", FALSE), 1, -1));
    }

    /*
     * load news @i@ and return text
     */

    function shortName($name, $l) {
        if (strlen($name) > $l) {
            $name = substr($name, 0, $l - 3) . '...';
        }
        return $name;
    }

    function loadnewssimple($i) {
        global $lang;
        global $conf;
        $newsurl = $this->getnewsurl($i);
        $newsdata = io_readFile($newsurl, false);
        return $newsdata;
    }

    function rendernews($i, $class) {
        if (file_exists($this->getnewsurl($i))) {
            $to_page.='<div class="' . $class . '">';
            /*
             * find news autor title and date news and render then
             */
            $newsdata = $this->extractParamtext($this->loadnewssimple($i));

            $to_page.= p_render("xhtml", p_get_instructions('<newsdate>' . $newsdata['newsdate'] . '-render</newsdate>'), $info);
            $to_page.='<div class="fksnewsheadline">';
            $to_page.= p_render("xhtml", p_get_instructions('===' . $newsdata['name'] . '==='), $info);
            $to_page.="</div>";
            $to_page.='<div class="fksnewsarticle">';
            $to_page.= p_render("xhtml", p_get_instructions($newsdata['text']), $info);
            $to_page.="</div>";
            $to_page.= p_render("xhtml", p_get_instructions('<newsauthor>[[' . $newsdata['email'] . '|' . $newsdata['author'] . ']]-render</newsauthor>'), $info);
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

    function findimax() {

        for ($i = 1; true; $i++) {
            $newsurl = $this->getnewsurl($i);
            if (file_exists($newsurl)) {
                continue;
            } else {
                $imax = $i;
                break;
            }
        }
        return $imax;
    }

    function controlData() {
        global $Rdata;
        for ($i = 0; true; $i++) {
            if (!array_key_exists('newson' . $i, $Rdata) && !array_key_exists('newsonR' . $i, $Rdata)) {
                break;
            } else {
                if ($Rdata['newson' . $i]) {
                    if ($Rdata['newsonR' . $i] == "T") {
                        if ($Rdata['newson' . $i] < $Rdata["maxnews"]) {
                            $data.=';' . $Rdata['newson' . $i] . ';';
                            //echo $i;
                        }
                    }
                }
            }
        }

        if (!$data) {

            $to_page.='<div class="error">'
                    . $this->getLang('dataerror') . "</div>";
        } else {
            $wfile = file_put_contents("data/meta/newsfeed.csv", $data);

            msg('New data: <br>' . $data, 0);
            if ($wfile) {
                msg(' written successful', 1);
            } else {
                msg("written failure", -1);
            }
        }
        return $to_page;
    }

    function fksnewsboolswitch($color1, $color2, $bool) {
        if ($bool) {
            return $color1;
        } else {
            return $color2;
        }
    }

    function returnMenu($lmenu) {
        global $lang;
        $form = new Doku_Form(array(
            'id' => "addtowiki",
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin"
        ));
        $form->addElement(makeHeading($this->getLang($lmenu), array()));
        $form->addElement(form_makeButton('submit', '', $this->getLang('returntomenu')));
        html_form('addnews', $form);
    }
    
     function lostNews() {
        $allnews = glob($this->getnewsurl("*"));
        $arraynews = array();
        foreach ($allnews as $key => $value) {
            $arraynews[] = substr($value, count($this->getnewsurl("*")) - 6, -4);
        }
        msg('Zabudol si ake id ma tva novinka?', 0);
        $form = new Doku_Form(array('id' => "load_new", 'onsubmit' => "return false"));
        $form->addElement(form_makeDatalistField('news_id_lost', 'lost_n', $arraynews));
        $form->addElement(form_makeButton('submit', '', $this->getLang('findnews')));
        $form->addElement('<div id="lost_news"> </div>');
        html_form('editnews', $form);
    }

}

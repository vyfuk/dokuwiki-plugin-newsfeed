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

    // public $this->FKSnews=new fksnews('name', 'author', 'email', 'newsdate', 'text', 'shortname', 'text-html', 'fullhtml', 'divhtml');;
    //public function __construct() {
    //    $this->FKSnews = new fksnewsfeed_news($this->getConf('wsdl'), $this->getConf('fksdb_login'), $this->getConf('fksdb_password'));
    // }
    // private $FKSnews = array('name', 'author', 'email', 'newsdate', 'text', 'shortname', 'text-html', 'fullhtml', 'divhtml');

    function getfulldata($no, $Sdata) {
        //print_r($Sdata);
        $data = array();
        $data['id'] = $no;
        $data['stream'] = $Sdata['stream'];
        $data['dir'] = $Sdata['dir'];
        //print_r($data);

        $data = array_merge($data, $this->extractParamtext($this->loadnewssimple($data)));


        $data['text-html'] = p_render("xhtml", p_get_instructions($data["text"]), $info);
        $data["fullhtml"] = $this->rendernews($data);

        return $data;
    }

    function getnewsurl($data) {
        global $conf;
        return str_replace("@i@", $data['id'], DOKU_INC . 'data/pages/fksnewsfeed/'
                . $data['dir'] . '/' . $this->getConf('newsfile') . '.txt');
    }

    /*
     * get wiki URL with :
     */

    function getwikinewsurl($i) {
        global $conf;
        $url = str_replace("@i@", $i, $this->getConf('newsfolder') . ':' . $this->getConf('newsfile'));
        return $url;
    }

    /*
     * changed doku text and extract param
     */

    function extractParamACT($text) {
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

        list($text, $param['text']) = preg_split('/\>/', str_replace("\n", '', $text));
        foreach (preg_split('/;/', $text)as $key => $value) {
            list($k, $v) = preg_split('/=/', $value);
            $param[$k] = $v;
        }
        $param['text-html'] = p_render("xhtml", p_get_instructions($param["text"]), $info);
        return $param;
    }

    /*
     * save a new file with value od USer
     */

    function saveNewNews($Rdata) {
        global $INFO;
        $fksnews.="<newsdate>" . dformat() . "</newsdate>\n"
                . "<newsauthor>[[" . $INFO['userinfo']['mail'] . '|' . $INFO['userinfo']['name'] . "]]</newsauthor>"
                . "\n"
                . "==== Název aktuality ==== \n"
                . "Tady napiš text aktuality.\n"
                . "\n";
        $Wnews = file_put_contents($this->getnewsurl($Rdata['newsid']), $fksnews);
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
     * load file with configuration
     */

    function loadstream($Sdata) {
        if (isset($Sdata['stream'])) {
            return preg_split('/;;/', substr(io_readFile("data/pages/fksnewsfeed/" . $Sdata['stream'] . "/newsfeed.csv", FALSE), 1, -1));
        } else {
            return preg_split('/;;/', substr(io_readFile("data/pages/fksnewsfeed/" . $Sdata['dir'] . "/newsfeed.csv", FALSE), 1, -1));
        }
    }

    /*
     * short name of news and add dots
     */

    function shortName($name, $l) {
        if (strlen($name) > $l) {
            $name = substr($name, 0, $l - 3) . '...';
        }
        return $name;
    }

    /*
     * load news @i@ and return text
     */

    function loadnewssimple($data) {
        return io_readFile($this->getnewsurl($data), false);
    }

    function renderfullnews($data) {
        return p_render("xhtml", p_get_instructions(io_readFile($this->getnewsurl($data))), $info);
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

        msg('Zabudol si ake id ma tva novinka?', 0);
        $form = new Doku_Form(array('id' => "load_new", 'onsubmit' => "return false"));
        $form->addElement(form_makeDatalistField('news_id_lost', 'lost_n', $this->allNews()));
        $form->addElement(form_makeButton('submit', '', $this->getLang('findnews')));
        $form->addElement('<div id="lost_news"> </div>');
        html_form('editnews', $form);
    }

    function allNews() {
        $allnews = glob($this->getnewsurl("*"));
        $arraynews = array();
        foreach ($allnews as $key => $value) {
            $arraynews[] = substr($value, count($this->getnewsurl("*")) - 6, -4);
        }
        return$arraynews;
    }

    function getNewsFile($news) {
        $id = $this->getPluginName() . ":$news";
        return metaFN($id, '.txt');
    }

    /*
     * function to rendering news (fksnewsfeed)
     */

    function rendernews($data) {
        //print_r($date);
        $to_page.=$this->newsdate($data['newsdate']);
        $to_page.=$this->newsheadline($data['name']);
        $to_page.=$this->newsarticle($data['text-html']);
        $to_page.=$this->newsauthor($data['email'], $data['author']);

        //$to_page.= p_render("xhtml", p_get_instructions('[[' . $data['email'] . '|' . $data['author'] . ']]'), $info);
        $to_page.='<div class="clearer"></div>';


        return $to_page;
    }

    function newsdate($date) {
        $enmonth = Array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December');
        $langmonth = Array(
            $this->getLang('jan'),
            $this->getLang('feb'),
            $this->getLang('mar'),
            $this->getLang('apr'),
            $this->getLang('may'),
            $this->getLang('jun'),
            $this->getLang('jul'),
            $this->getLang('aug'),
            $this->getLang('sep'),
            $this->getLang('oct'),
            $this->getLang('now'),
            $this->getLang('dec')
        );


        return '<div class="fksnewsdate">' . str_replace($enmonth, $langmonth, $date) . '</div>';
    }

    function newsheadline($headline) {
        return '<div class="fksnewsheadline">'
                . p_render("xhtml", p_get_instructions('===' . $headline . '==='), $info)
                . '</div>';
    }

    function newsarticle($texthtml) {
        return '<div class="fksnewsarticle">' . $texthtml . "</div>";
    }

    function newsauthor($email, $author) {
        return '<div class="fksnewsauthor">' . p_render("xhtml", p_get_instructions('[[' . $email . '|' . $author . ']]'), $info) . '</div>';
    }

}

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

        $data = array();
        $data['id'] = $no;
        $data['stream'] = $Sdata['stream'];
        $data['dir'] = $Sdata['dir'];
        $data = array_merge($data, $this->extractParamtext($this->loadnewssimple($data)));
        $data['text-html'] = p_render("xhtml", p_get_instructions($data["text"]), $info);
        $data["fullhtml"] = $this->rendernews($data);

        return $data;
    }


    /*
     * changed doku text and extract param
     */

    function extractParamACT($text) {
        global $TEXT;
        $param = $this->extractParamtext($text);
        $TEXT = $param["text"];
        unset($param["text"]);
        return $param;
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
            return preg_split('/;;/', substr(io_readFile("data/pages/fksnewsfeed/streams/" . $Sdata['stream'] . ".csv", FALSE), 1, -1));
        } else {
            return preg_split('/;;/', substr(io_readFile("data/pages/fksnewsfeed/" . $Sdata['dir'] . "/newsfeed.csv", FALSE), 1, -1));
        }
    }

    function loadstreamdir($Sdata) {
        return preg_split('/;;/', substr(io_readFile("data/pages/fksnewsfeed/" . $Sdata['dir'] . "/newsfeed.csv", FALSE), 1, -1));
    }

    /*
     * load news @i@ and return text
     */

    function loadnewssimple($data) {
        return io_readFile($this->getnewsurl($data), false);
    }

    function renderfullnews($data) {
        return '<div class="' . $data['even']
                . '">'
                . p_render("xhtml", p_get_instructions(io_readFile($this->getnewsurl($data))), $info)
                . '</div>';
    }

    function findimax($dir) {
        for ($i = 1; true; $i++) {
            if (file_exists($this->getnewsurl(array('dir' => $dir, 'id' => $i)))) {
                continue;
            } else {
                $imax = $i;
                break;
            }
        }
        return $imax;
    }

    function fksnewsboolswitch($color1, $color2, $bool) {
        if ($bool) {
            return $color1;
        } else {
            return $color2;
        }
    }

    function lostNews() {

        msg('Zabudol si ake id ma tva novinka?', 0);
        $form = new Doku_Form(array('id' => "load_new", 'onsubmit' => "return false"));
        $form->addElement(form_makeDatalistField('news_lost_dir', 'list', array('start', 'somtthing else')));
        $form->addElement(form_makeDatalistField('news_id_lost', 'lost_n', $this->allNews($dir)));
        $form->addElement(form_makeButton('submit', '', $this->getLang('findnews')));
        $form->addElement('<div id="lost_news"> </div>');
        html_form('editnews', $form);
    }

    function allNews($dir) {

        $dir = 'start';

        $allnews = glob($this->getnewsurl(array('dir' => $dir, 'id' => "*")));

        $arraynews = array();
        foreach ($allnews as $key => $value) {
            $arraynews[] = substr(str_replace(DOKU_INC, '', $value), strlen("data/pages/fksnewsfeed/" . $dir . "/news"), -4);
            //$arraynews[] = substr($value, count($this->getnewsurl(array('dir' => $dir, 'id' => "*"))) - 6, -4);
        }

        return$arraynews;
    }

    function getNewsFile($news) {
        $id = $this->getPluginName() . ":$news";
        return metaFN($id, '.txt');
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * 
     * Control data before wrinting
     * 
     */

    function controlData() {

        global $Rdata;
        for ($i = 1; true; $i++) {
            if (!array_key_exists('newson' . $i, $Rdata) && !array_key_exists('newsonR' . $i, $Rdata)) {
                break;
            } else {
                if ($Rdata['newson' . $i] && $Rdata['newsonR' . $i] == "T") {
                    switch ($Rdata['type']) {
                        case 'stream':
                            $data.=';' . $Rdata['newson' . $i] . '-' . $Rdata['newsdiron' . $i] . ';';
                            break;
                        case 'dir':
                            $data.=';' . $Rdata['newson' . $i] . ';';
                            break;
                    }
                }
            }
        }
        //echo $data;
        msg('New data: <br>' . $data, 0);
        if (!$data) {
            msg($this->getLang('dataerror'), -1);
        } else {
            switch ($Rdata['type']) {
                case 'stream':
                    $wfile = file_put_contents(DOKU_INC . "data/pages/fksnewsfeed/streams/" . $Rdata['stream'] . ".csv", $data);
                    break;
                case 'dir':
                    $wfile = file_put_contents(DOKU_INC . "data/pages/fksnewsfeed/" . $Rdata['dir'] . "/newsfeed.csv", $data);
                    break;
            }
            if ($wfile) {
                msg('written successful', 1);
            } else {
                msg("written failure", -1);
            }
        }
        return;
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * save a new file with value od USer
     */

    function saveNewNews($Rdata) {
        global $INFO;
        $fksnews.= '<fksnewsfeed
newsdate=' . dformat() . ';
author=' . $INFO['userinfo']['name'] . ';
email= ' . $INFO['userinfo']['mail'] . ';
name=Název aktuality>
Tady napiš text aktuality
</fksnewsfeed>';
        $Wnews = file_put_contents($this->getnewsurl(array('id' => $Rdata['newsid'], 'dir' => $Rdata['dir'])), $fksnews);
        return $Wnews;
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * 
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
     * © Michal Červeňák
     * 
     * 
     * short name of news and add dots
     */

    function shortName($name, $l) {
        if (strlen($name) > $l) {
            $name = substr($name, 0, $l - 3) . '...';
        }
        return $name;
    }

    /*
     * © Michal Červeňák
     * 
     * render stream or dir news
     * 
     */

    function renderstream($Sdata) {

        foreach ($this->loadstream($Sdata) as $key => $value) {

            if (isset($Sdata['stream'])) {
                list($id, $dir) = preg_split('/-/', $value);
            } else {
                $id = $value;
                $dir = $Sdata['dir'];
            }
            if ($Sdata['feed']) {
                if ($Sdata['feed'] % 2) {
                    $to_page.=$this->renderfullnews(array('dir' => $dir, 'id' => $id, 'even' => 'fksnewseven'));
                } else {
                    $to_page.=$this->renderfullnews(array('dir' => $dir, 'id' => $id, 'even' => 'fksnewsodd'));
                }

                $Sdata['feed'] --;
            } else {
                break;
            }
        }
        return $to_page;
    }

    /*
     * 
     * © Michal Červeňák
     * 
     * function to rendering news (fksnewsfeed)
     */

    function rendernews($data) {

        $to_page.=$this->newsdate($data['newsdate']);
        $to_page.=$this->newsheadline($data['name']);
        $to_page.=$this->newsarticle($data['text-html']);
        $to_page.=$this->newsauthor($data['email'], $data['author']);
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

    /*
     * © Michal Červeňák
     * 
     * 
     * function to get links 
     */

    function getnewsurl($data) {

        return str_replace(":", '/', DOKU_INC . 'data/pages/' . $this->getwikinewsurl($data) . '.txt');
    }

    /*
     * get wiki URL with :
     */

    function getwikinewsurl($data) {
        return str_replace("@i@", $data['id'], 'fksnewsfeed:' . $data['dir'] . ':' . $this->getConf('newsfile'));
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * talčítko pre návrat do menu z admin prostredia (možno do pluginu fksadminpage ?FR
     */

    function returnMenu($lmenu) {
        global $lang;
        $form = new Doku_Form(array(
            'id' => "returntomenu",
            'method' => 'POST',
            'action' => DOKU_BASE . "?do=admin"
        ));
        $form->addElement(makeHeading($this->getLang($lmenu), array()));
        $form->addElement(form_makeButton('submit', '', $this->getLang('returntomenu')));
        html_form('returntomenu', $form);
    }

    /*
     * 
     * © Michal Červeňák
     * 
     * Changing dir and stream in adminpage.
     * 
     */

    function changedir() {
        global $lang;

        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
                //'action' => DOKU_BASE . "?do=admin&page=fksnewsfeed_permutview"
        ));
        $form->addElement(makeHeading($this->getLang('changedir'), array()));
        $form->addElement(form_makeDatalistField('dir', 'stream', array('start'), $this->getLang('changedir')));
        $form->addHidden('type', 'dir');

        $form->addElement(form_makeButton('submit', '', $this->getLang('changedir')));
        html_form('changedirnews', $form);
    }

    function changedstream() {
        global $lang;
        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
                //'action' => DOKU_BASE . "?do=admin&page=fksnewsfeed_permutview"
        ));
        $form->addElement(makeHeading($this->getLang('changedir'), array()));
        $form->addElement(form_makeDatalistField('stream', 'stream', array('start'), $this->getLang('changestream')));
        $form->addHidden('type', 'stream');

        $form->addElement(form_makeButton('submit', '', $this->getLang('changedir')));
        html_form('changedirnews', $form);
    }

}

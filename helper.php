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

    public $Fields = array('name', 'email', 'author', 'newsdate', 'text');
    public $FKS_helper;

    public function __construct() {

        $this->FKS_helper = $this->loadHelper('fkshelper');
    }

    /* function getfulldata($no, $Sdata) {

      $data = array();
      $data['id'] = $no;
      $data['stream'] = $Sdata['stream'];
      //$data['dir'] = $Sdata['dir'];
      $data = array_merge($data, $this->extractParamtext_feed($this->loadnewssimple($data)));
      $data['text-html'] = p_render("xhtml", p_get_instructions($data["text"]), $info);
      $data["fullhtml"] = $this->rendernews($data);

      return $data;
      } */



    /*

     * delete casche when is run
     */

    function deletecache() {

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

    function loadstream($s) {
        return preg_split('/;;/', substr(io_readFile(metaFN("fksnewsfeed:streams:" . $s, ".csv"), FALSE), 1, -1));
    }

    function renderfullnews($id, $even = "fkseven") {
        $r = '<div class="' . $even
                . '">'
                . p_render("xhtml", p_get_instructions('<fksnewsfeed id=' . $id . '>'), $info)
                . '</div>';
        return $r;
    }

    public function findimax() {
        for ($i = 1; true; $i++) {
            if (file_exists(metaFN($this->getwikinewsurl($i), '.txt'))) {
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

    public function lostNews() {
        $form = new Doku_Form(array('id' => "load_new", 'onsubmit' => "return false"));
        $form->startFieldset($this->getLang('findnews'));
        $form->addElement($this->FKS_helper->returnmsg('Zabudol si ake id ma tva novinka?', 0));
        $form->addElement(form_makeTextField('news_id_lost', null, $this->getLang('id')));
        $form->addElement(form_makeButton('submit', '', $this->getLang('findnews')));
        $form->endFieldset();
        $form->addElement(form_makeOpenTag('div', array('id' => 'lost_news')));
        $form->addElement(form_makeCloseTag('div'));
        html_form('editnews', $form);
    }

    function allNews($dir = 'feeds') {
        $arraynews = array();
        foreach ($this->allshortnews() as $key => $value) {
            $arraynews[] = $this->shortfilename($value, 'fksnewsfeed/' . $dir, 'ID_ONLY');
        }

        return $arraynews;
    }

    public function shortfilename($name, $dir, $flag = 'ID_ONLY', $type = 4) {
        switch ($flag) {
            case 'ID_ONLY':
                $n = substr($name, strlen(DOKU_INC . "data/meta/" . $dir . "/news"), -$type);
                break;
            case 'NEWS_W_ID':
                $n = substr($name, strlen(DOKU_INC . "data/meta/" . $dir . "/"), -$type);
                break;
            case 'DIR_N_ID':
                $n = substr($name, strlen(DOKU_INC . "data/meta/"), -$type);
                break;
        }
        return $n;
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

    function controlData($Rdata) {
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

    function saveNewNews($Rdata, $link) {

        if (file_exists(metaFN($link, '.txt'))) {
            return FALSE;
        }
        foreach ($this->Fields as $v) {
            if (array_key_exists($v, $Rdata)) {
                $data[$v] = $Rdata[$v];
            } else {
                $data[$v] = $this->getConf($v);
            }
        }
        $fksnews.= '<fksnewsfeed
newsdate=' . $data['newsdate'] . ';
author=' . $data['author'] . ';
email= ' . $data['email'] . ';
name=' . $data['name'] . '>
' . $data['text'] . '
</fksnewsfeed>';
        $Wnews = io_saveFile(metaFN($link, '.txt'), $fksnews);
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
        list($text, $param['text']) = preg_split('/\>/', str_replace(array("\n", '<fksnewsfeed', '</fksnewsfeed>'), array('', '', ''), $text), 2);
        foreach (preg_split('/;/', $text)as $value) {
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

    function shortName($name = "", $l = 25) {
        if (strlen($name) > $l) {
            $name = mb_substr($name, 0, $l - 3) . '...';
        }
        return $name;
    }

    /*
     * 
     * © Michal Červeňák
     * 
     * function to rendering news to template(fksnewsfeed)
     */



    /*
     * get wiki URL with :
     */

    public function getwikinewsurl($id) {
        return str_replace("@i@", $id, 'fksnewsfeed:feeds:' . $this->getConf('newsfile'));
    }

    /*
     * 
     * © Michal Červeňák
     * 
     * Changing dir and stream in adminpage.
     * 
     */

    public function changedir() {
        $form = new Doku_Form(array(
            'id' => "changedir",
            'method' => 'POST',
        ));
        $form->startFieldset($this->getLang('changedir'));
        $form->addElement(form_makeDatalistField('dir', 'dir', $this->alldir(), $this->getLang('dir')));
        $form->addHidden('type', 'dir');
        $form->addElement(form_makeButton('submit', '', $this->getLang('changedir')));
        $form->endFieldset();
        //html_form('changedirnews', $form);
    }

    /**
     * © Michal Červeňák
     * 
     * 
     * return all dir and streams


      function alldir() {
      foreach (array_filter(glob(DOKU_INC . 'data/pages/fksnewsfeed/*'), 'is_dir') as $key => $value) {
      if ($value != DOKU_INC . 'data/pages/fksnewsfeed/streams') {
      $dirs[$key] = str_replace(DOKU_INC . 'data/pages/fksnewsfeed/', "", $value);
      }
      } return $dirs;
      }
     */
    function allstream() {
        foreach (glob(DOKU_INC . 'data/pages/fksnewsfeed/streams/*.csv') as $key => $value) {

            $streams[$key] = str_replace(array(DOKU_INC . 'data/pages/fksnewsfeed/streams/', '.csv'), array("", ''), $value);
        }
        return $streams;
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * 
     * msg info about set strem or dir 
     */

    function addlocation($Rdata) {
        return $this->FKS_helper->returnmsg('zobrazuje sa ' . $this->getLang($Rdata['type']) . ' <b>' . $Rdata['dir'] . $Rdata['stream'] . '</b>', 1);
    }

    function allshortnews() {
        $allnews = glob(DOKU_INC . 'data/meta/fksnewsfeed/feeds/*.txt');

        sort($allnews, SORT_NATURAL | SORT_FLAG_CASE);

        //var_dump($allnews);
        return $allnews;
    }

}

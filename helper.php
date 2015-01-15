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
    public $simple_tpl;

    const simple_tpl = "{{fksnewsfeed>id=@id@; even=@even@}}";

    public function __construct() {
        $this->simple_tpl = self::simple_tpl;

        $this->FKS_helper = $this->loadHelper('fkshelper');
    }

    /*
     * load file with configuration
     */

    public function loadstream($s, $o = true) {
        if ($o) {
            return preg_split('/;;/', substr(io_readFile(metaFN("fksnewsfeed:streams:" . $s, ".csv"), FALSE), 1, -1));
        } else {

            $arr = preg_split("/\n/", substr(io_readFile(metaFN("fksnewsfeed:old-streams:" . $s, ".csv"), FALSE), 1, -1));
            $l = count($arr);
            return preg_split('/;;/', substr($arr[$l - 1], 1, -1));
        }
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

    public function allNews($dir = 'feeds') {
        $arraynews = array();
        foreach ($this->allshortnews() as $key => $value) {
            $arraynews[] = $this->shortfilename($value, 'fksnewsfeed/' . $dir, 'ID_ONLY');
        }

        return $arraynews;
    }

    public function shortfilename($name, $dir, $flag = 'ID_ONLY', $type = 4) {
        if (!preg_match('/\w*\/\z/', $dir)) {
            //$dir = $dir . DIRECTORY_SEPARATOR;
        }
        $doku = pathinfo(DOKU_INC);

        $rep_dir = $doku['dirname'] . DIRECTORY_SEPARATOR . $doku['filename'] . DIRECTORY_SEPARATOR . "data/meta/";
        switch ($flag) {
            case 'ID_ONLY':
                $rep_dir.=$dir . "/news";
                break;
            case 'NEWS_W_ID':
                $rep_dir.=$dir . "/";
                break;
            case 'DIR_N_ID':
                $rep_dir.='';
                break;
        }
        $n = str_replace($rep_dir, '', $name);
        $n = substr($n, 0, -$type);
        return $n;
    }

    /*
     * © Michal Červeňák
     * 
     * 
     * save a new file with value od USer
     */

    public function saveNewNews($Rdata, $link, $rw = false) {
        if (!$rw) {
            if (file_exists(metaFN($link, '.txt'))) {
                return FALSE;
            }
        }
        foreach ($this->Fields as $v) {
            if (array_key_exists($v, $Rdata)) {
                $data[$v] = $Rdata[$v];
            } else {
                $data[$v] = $this->getConf($v);
            }
        }
        $fksnews.=
                'newsdate=' . $data['newsdate'] . ';
author=' . $data['author'] . ';
email= ' . $data['email'] . ';
name=' . $data['name'] . '>
' . $data['text'];
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

    /* function extractParamtext($text) {
      list($text, $param['text']) = preg_split('/\>/', str_replace(array("\n", '<fksnewsfeed', '</fksnewsfeed>'), array('', '', ''), $text), 2);
      foreach (preg_split('/;/', $text)as $value) {
      list($k, $v) = preg_split('/=/', $value);
      $param[$k] = $v;
      }
      $param['text-html'] = p_render("xhtml", p_get_instructions($param["text"]), $info);
      return $param;
      } */

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

    function allstream() {
        foreach (glob(DOKU_INC . 'data/meta/fksnewsfeed/streams/*.csv') as $key => $value) {

            $streams[$key] = str_replace(array(DOKU_INC . 'data/meta/fksnewsfeed/streams/', '.csv'), array("", ''), $value);
        }
        return $streams;
    }

    function allshortnews() {
        $allnews = glob(DOKU_INC . 'data/meta/fksnewsfeed/feeds/*.txt');

        sort($allnews, SORT_NATURAL | SORT_FLAG_CASE);

        //var_dump($allnews);
        return $allnews;
    }

    public function _log_event($type, $newsid) {
        global $INFO;

        $log = io_readFile(metaFN('fksnewsfeed:log', '.log'));
        $newsid = preg_replace('/[A-Z]/', '', $newsid);
        $log.= "\n" . date("Y-m-d H:i:s") . ' ; ' . $newsid . ' ; ' . $type . ' ; ' . $INFO['name'] . ' ; ' . $_SERVER['REMOTE_ADDR'] . ';' . $INFO['ip'] . ' ; ' . $INFO['user'];

        io_saveFile(metaFN('fksnewsfeed:log', '.log'), $log);
    }

}

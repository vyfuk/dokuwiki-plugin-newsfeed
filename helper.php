<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
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

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $s 
     * @param bool $o
     * @return void
     * load file with configuration
     * and load old configuration file 
     */
    public static function loadstream($s, $o = true) {
        if ($o) {
            return (array) preg_split('/;;/', substr(io_readFile(metaFN("fksnewsfeed:streams:" . $s, ".csv"), FALSE), 1, -1));
        } else {

            $arr = preg_split("/\n/", substr(io_readFile(metaFN("fksnewsfeed:old-streams:" . $s, ".csv"), FALSE), 1, -1));
            $l = count($arr);
            return (array) preg_split('/;;/', substr($arr[$l - 1], 1, -1));
        }
    }

    /**
     * Find no news 
     * @author Michal Červeňák <miso@fykos.cz>
     * @return int
     */
    public function findimax() {
        for ($i = 1; true; $i++) {
            if (file_exists(metaFN($this->getwikinewsurl($i), '.txt'))) {
                continue;
            } else {
                $imax = $i;
                break;
            }
        }
        return (int) $imax;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $dir
     * @return array
     */
    public static function allNews($dir = 'feeds') {
        $arraynews = array();
        foreach ($this->allshortnews() as $key => $value) {
            $arraynews[] = $this->shortfilename($value, 'fksnewsfeed/' . $dir, 'ID_ONLY');
        }

        return (array) $arraynews;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name
     * @param string $dir
     * @param flag $flag
     * @param int $type
     * @return string
     */
    public static function shortfilename($name, $dir = '', $flag = 'ID_ONLY', $type = 4) {
        if (!preg_match('/\w*\/\z/', $dir)) {
            //$dir = $dir . DIRECTORY_SEPARATOR;
        }
        $doku = pathinfo(DOKU_INC);

        $rep_dir_base = $doku['dirname'] . DIRECTORY_SEPARATOR . $doku['filename'] . DIRECTORY_SEPARATOR;
        $rep_dir_base_full = $doku['dirname'] . DIRECTORY_SEPARATOR . $doku['filename'] . '.' . $doku['extension'] . DIRECTORY_SEPARATOR;
        $rep_dir = "data/meta/";
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
        $n = str_replace(array($rep_dir_base_full, $rep_dir, $rep_dir_base), '', $name);
        $n = substr($n, 0, -$type);
        return (string) $n;
    }

    /**
     * save a new news or rewrite old
     * @author Michal Červeňák <miso@fykos.cz>
     * @return bool is write ok
     * @param array $Rdata params to save
     * @param string $link path to news
     * @param bool $rw rewrite?
     * 
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
        return (bool) $Wnews;
    }

    /**
     * short name of news and add dots
     * 
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name text to short
     * @param int $l length of output
     * @return string shorted text
     * 
     * 
     */
    public static function shortName($name = "", $l = 25) {
        if (strlen($name) > $l) {
            $name = mb_substr($name, 0, $l - 3) . '...';
        }
        return (string) $name;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param int $id no of news
     * @return string path news
     */
    public function getwikinewsurl($id) {
        return (string) str_replace("@i@", $id, 'fksnewsfeed:feeds:' . $this->getConf('newsfile'));
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array all stream from dir
     */
    public static function allstream() {
        foreach (glob(DOKU_INC . 'data/meta/fksnewsfeed/streams/*.csv') as $key => $value) {
            $sh = self::shortfilename($value, 'fksnewsfeed/streams', 'NEWS_W_ID', 4);
          
            $streams[$key]=$sh;
            //$streams[$key] = str_replace(array(DOKU_INC . 'data/meta/fksnewsfeed/streams/', '.csv'), array("", ''), $value);
        }
        return (array) $streams;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array
     */
    public static function allshortnews() {

        $allnews = glob(DOKU_INC . 'data/meta/fksnewsfeed/feeds/*.txt');
        sort($allnews, SORT_NATURAL | SORT_FLAG_CASE);
        return (array) $allnews;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @global type $INFO
     * @param string $type action 
     * @param string $newsid
     * @return void
     */
    public static function _log_event($type, $newsid) {
        global $INFO;

        $log = io_readFile(metaFN('fksnewsfeed:log', '.log'));
        $newsid = preg_replace('/[A-Z]/', '', $newsid);
        $log.= "\n" . date("Y-m-d H:i:s") . ' ; ' . $newsid . ' ; ' . $type . ' ; ' . $INFO['name'] . ' ; ' . $_SERVER['REMOTE_ADDR'] . ';' . $INFO['ip'] . ' ; ' . $INFO['user'];

        io_saveFile(metaFN('fksnewsfeed:log', '.log'), $log);
        return;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param int $i
     * @return string
     */
    public static function _is_even($i) {
        if ($i % 2) {
            return 'FKS_newsfeed_even';
        } else {
            return 'FKS_newsfeed_odd';
        }
    }

}

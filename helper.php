<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

if(!defined('DOKU_LF')){
    define('DOKU_LF',"\n");
}
if(!defined('DOKU_TAB')){
    define('DOKU_TAB',"\t");
}

class helper_plugin_fksnewsfeed extends DokuWiki_Plugin {

    public static $Fields = array('name','email','author','newsdate','image','text');
    public $FKS_helper;
    public $simple_tpl;
    public $sqlite;

    const simple_tpl = "{{fksnewsfeed>id=@id@; even=@even@}}";
    const db_table_feed = "fks_newsfeed_news";
    const db_table_dependence = "fks_newsfeed_dependence";
    const db_table_order = "fks_newsfeed_order";
    const db_table_stream = "fks_newsfeed_stream";
    const db_view_dependence = "v_dependence";

    public function __construct() {
        $this->simple_tpl = self::simple_tpl;
        $this->FKS_helper = $this->loadHelper('fkshelper');

        $this->sqlite = $this->loadHelper('sqlite',false);
        $pluginName = $this->getPluginName();
        if(!$this->sqlite){
            msg($pluginName.': This plugin requires the sqlite plugin. Please install it.');
            return;
        }
        if(!$this->sqlite->init('fksnewsfeed',DOKU_PLUGIN.$pluginName.DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR)){
            msg($pluginName.': Cannot initialize database.');
            return;
        }
    }

    public function stream_to_id($stream) {
        $sql1 = 'select stream_id from '.self::db_table_stream.' where name=?';
        $res1 = $this->sqlite->query($sql1,$stream);
        return (int) $this->sqlite->res2single($res1);
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $s 
     * @param bool $o
     * @return void
     * load file with configuration
     * and load old configuration file 
     */
    public function loadstream($stream) {
        $stream_id = $this->stream_to_id($stream);
        $sql = 'SELECT * FROM '.self::db_table_order.' where stream_id=? AND weight != 0 ORDER BY weight';
        $res = $this->sqlite->query($sql,$stream_id);
        return (array) array_reverse($this->sqlite->res2arr($res));
    }

    /**
     * Find no news 
     * @author Michal Červeňák <miso@fykos.cz>
     * @return int
     */
    public function findimax() {
        $sql2 = 'select max(news_id) from '.self::db_table_feed;
        $res = $this->sqlite->query($sql2);
        $imax = $this->sqlite->res2single($res);

        return (int) $imax;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $name
     * @param string $dir
     * @param flag $flag
     * @param int $type
     * @return string
     */
    public static function shortfilename($name,$dir = '',$flag = 'ID_ONLY',$type = 4) {
        if(!preg_match('/\w*\/\z/',$dir)){
            //$dir = $dir . DIRECTORY_SEPARATOR;
        }
        $doku = pathinfo(DOKU_INC);

        $rep_dir_base = $doku['dirname'].DIRECTORY_SEPARATOR.$doku['filename'].DIRECTORY_SEPARATOR;
        $rep_dir_base_full = $doku['dirname'].DIRECTORY_SEPARATOR.$doku['filename'].'.'.$doku['extension'].DIRECTORY_SEPARATOR;
        $rep_dir = "data/meta/";
        switch ($flag) {
            case 'ID_ONLY':
                $rep_dir.=$dir."/news";
                break;
            case 'NEWS_W_ID':
                $rep_dir.=$dir."/";
                break;
            case 'DIR_N_ID':
                $rep_dir.='';
                break;
        }
        $n = str_replace(array($rep_dir_base_full,$rep_dir,$rep_dir_base),'',$name);

        return (string) substr($n,0,-$type);
    }

    /**
     * save a new news or rewrite old
     * @author Michal Červeňák <miso@fykos.cz>
     * @return bool is write ok
     * @param array $Rdata params to save
     * @param string $id path to news
     * @param bool $rw rewrite?
     * 
     */
    public function saveNewNews($Rdata,$id = 0,$rw = false) {

        foreach (self::$Fields as $v) {
            if(array_key_exists($v,$Rdata)){
                $data[$v] = $Rdata[$v];
            }else{
                $data[$v] = $this->getConf($v);
            }
        }


        $image = $data['image'];
        $date = $data['newsdate'];
        $author = $data['author'];
        $email = $data['email'];
        $name = $data['name'];
        $text = $data['text'];
        if(!$rw){
            $sql = 'INSERT INTO '.self::db_table_feed.' (name, author, email,newsdate,text,image) VALUES(?,?,?,?,?,?) ;';
            $this->sqlite->query($sql,$name,$author,$email,$date,$text,$image);
            return $this->findimax();
        }else{
            $sql = 'UPDATE '.self::db_table_feed.' SET name=?, author=?, email=?, newsdate=?, text=?, image=? where news_id=? ';

            $this->sqlite->query($sql,$name,$author,$email,$date,$text,$image,$id);
            return $id;
        }
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
    public static function shortName($name = "",$l = 25) {
        if(strlen($name) > $l){
            $name = mb_substr($name,0,$l - 3).'...';
        }
        return (string) $name;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array all stream from dir
     */
    public static function allstream() {
        $streams = array();
        $sql = 'SELECT s.name FROM '.self::db_table_stream.' s';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return$streams;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @global type $INFO
     * @param string $type action 
     * @param string $newsid
     * @return void
     */
    public static function _log_event($type,$newsid) {
        global $INFO;

        $log = io_readFile(metaFN('fksnewsfeed:log','.log'));
        $news_id = preg_replace('/[A-Z]/','',$newsid);
        $log.= "\n".date("Y-m-d H:i:s").' ; '.$news_id.' ; '.$type.' ; '.$INFO['name'].' ; '.$_SERVER['REMOTE_ADDR'].';'.$INFO['ip'].' ; '.$INFO['user'];

        io_saveFile(metaFN('fksnewsfeed:log','.log'),$log);
        return;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param int $i
     * @return string
     */
    public static function _is_even($i) {
        return 'FKS_newsfeed_'.helper_plugin_fkshelper::_is_even($i);
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function _generate_token($id) {
        $hash_no = (int) $this->getConf('hash_no');
        $l = (int) $this->getConf('no_pref');
        $this->hash['pre'] = helper_plugin_fkshelper::_generate_rand($l);
        $this->hash['pos'] = helper_plugin_fkshelper::_generate_rand($l);
        $this->hash['hex'] = dechex($hash_no + 2 * $id);
        $this->hash['hash'] = $this->hash['pre'].$this->hash['hex'].$this->hash['pos'];
        return (string) DOKU_URL.'?do=fksnewsfeed_token&token='.$this->hash['hash'];
    }

    /**
     * load news @i@ and return text
     * @author     Michal Červeňák <miso@fykos.cz>
     * @param int $id
     * @return string
     */
    public function load_news_simple($id) {
        $sql = 'SELECT * FROM '.self::db_table_feed.' where news_id='.$id.'';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {

            return $row;
        }
    }

    public function all_values($field) {
        $values = array();
        $sql = 'SELECT t.? FROM '.self::db_table_feed.' t GROUP BY t.?';
        $res = $this->sqlite->query($sql,$field,$field);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $values[] = $row[$field];
        }
        return $values;
    }

    public function all_dependence($stream_id) {

        $stream_ids = array();
        $sql = 'SELECT * FROM '.self::db_table_dependence.' t WHERE t.dependence_to=?';
        $res = $this->sqlite->query($sql,$stream_id);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['dependence_from'];
        }
        return $stream_ids;
    }

    public function create_dependence($stream_id,&$arr) {

        foreach ($this->all_dependence($stream_id)as $new_stream_id) {
            if(!in_array($new_stream_id,$arr)){
                $arr[] = $new_stream_id;
                $this->create_dependence($new_stream_id,$arr);
            }
        }
    }

    public function save_to_stream($stream_id,$id,$weight = null) {
        if($weight === null){
            $sql2 = 'select max(weight) from '.self::db_table_order.' where stream_id=?';
            $res2 = $this->sqlite->query($sql2,$stream_id);
            $weight = (int) $this->sqlite->res2single($res2);
            $weight+=10;
        }

        $sql3 = 'INSERT INTO '.self::db_table_order.' (news_id,stream_id,weight) values(?,?,?)';
        $this->sqlite->query($sql3,$id,$stream_id,$weight);
        $sql4 = 'SELECT max(order_id) from '.self::db_table_order.'';
        $res4 = $this->sqlite->query($sql4);
        return (int) $this->sqlite->res2single($res4);
    }

    public function update_stream($weigth,$order_id) {
        $sql = 'UPDATE '.self::db_table_order.' SET weight=? WHERE order_id=?';
        return $this->sqlite->query($sql,$weigth,$order_id);
    }

    public function create_order_div($news_id,$order_id,$weight,$k = 0) {
        $form = "";
        $e = $this->_is_even($k);
        $n = str_replace(array('@id@','@even@'),array($news_id,$e),$this->simple_tpl);
        $form.= '<div class="FKS_newsfeed_delete_stream_news" data-index="'.$order_id.'" data-id="'.$news_id.'">';
        $form.='<div class="FKS_newsfeed_delete_stream_news_weight">           
                <button type="button" class="close FKS_newsfeed_delete_news" >
  <span aria-hidden="true">&times;</span>
</button>';
        $form.='<span>'.$this->getLang('weight').'</span><input class="edit" name="weight['.$order_id.']" value="'.$weight.'">';
        $form.='</div>';

        $form.=p_render("xhtml",p_get_instructions($n),$info);
        $form.='</div>';
        return $form;
    }

}

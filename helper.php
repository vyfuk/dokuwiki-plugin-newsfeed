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

    public static $Fields = array('name','email','author','newsdate','image','category','text');
    public $FKS_helper;
    public $simple_tpl;
    public $sqlite;
    public $errors;

    const simple_tpl = "{{fksnewsfeed>id=@id@; even=@even@; edited=@edited@;stream=@stream@}}";
    const db_table_feed = "fks_newsfeed_news";
    const db_table_dependence = "fks_newsfeed_dependence";
    const db_table_order = "fks_newsfeed_order";
    const db_table_stream = "fks_newsfeed_stream";
    const db_view_dependence = "v_dependence";

    public function __construct() {
        $this->simple_tpl = self::simple_tpl;
        $this->FKS_helper = $this->loadHelper('fkshelper');
        $this->errors = array();

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

    /**
     * 
     * @param type $stream
     * @return type
     */
    public function StreamToID($stream) {
        $sql1 = 'select stream_id from '.self::db_table_stream.' where name=?';
        $res1 = $this->sqlite->query($sql1,$stream);
        $stream_id = $this->sqlite->res2single($res1);
        if($stream_id == 0){
            $this->errors[] = _('Stream dont\' exist');
        }
        return (int) $stream_id;
    }

    public function stream_to_id($stream) {
        return $this->StreamToID($stream);
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $s 
     * @param bool $o
     * @return array
     * load file with configuration
     * and load old configuration file 
     */
    public function LoadStream($stream) {
        $stream_id = $this->StreamToID($stream);
        $sql = 'SELECT * FROM '.self::db_table_order.' o jOIN '.self::db_table_feed.' n ON o.news_id=n.news_id WHERE stream_id=? ';
        $res = $this->sqlite->query($sql,$stream_id);
        $ars = $this->sqlite->res2arr($res);

        foreach ($ars as $key => $ar) {
            if((time() < strtotime($ar['priority_from'])) || (time() > strtotime($ar['priority_to']))){
                $ars[$key]['priority'] = 0;
                
            }else{
               //var_dump($ar) ;
            }
            
            //var_dump(time() < strtotime($ar['priority_from']) || (time() > strtotime($ar['priority_to'])));
            
        }
        usort($ars,function ($a,$b) {
            if($a['priority'] > $b['priority']){
                return -1;
            }elseif($a['priority'] < $b['priority']){
                return 1;
            }else{
                return strcmp($b['newsdate'],$a['newsdate']);
            }
        });
        //var_dump($ars);
        return (array) $ars;
    }

    /**
     * Find no news 
     * @author Michal Červeňák <miso@fykos.cz>
     * @return int
     */
    public function FindMax() {
        $sql2 = 'select max(news_id) from '.self::db_table_feed;
        $res = $this->sqlite->query($sql2);
        $imax = $this->sqlite->res2single($res);

        return (int) $imax;
    }

    public function findimax() {
        return $this->FindMax();
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
     * @param array $data params to save
     * @param string $id path to news
     * @param bool $rw rewrite?
     * 
     */
    public function SaveNews($data,$id = 0,$rw = false) {
        $image = $data['image'];
        $date = $data['newsdate'];
        $author = $data['author'];
        $email = $data['email'];
        $name = $data['name'];
        $text = $data['text'];
        $category = $data['category'];
        if(!$rw){
            $sql = 'INSERT INTO '.self::db_table_feed.' (name, author, email,newsdate,text,image,category) VALUES(?,?,?,?,?,?,?) ;';
            $this->sqlite->query($sql,$name,$author,$email,$date,$text,$image,$category);
            return $this->FindMax();
        }else{
            $sql = 'UPDATE '.self::db_table_feed.' SET name=?, author=?, email=?, newsdate=?, text=?, image=?,category=? where news_id=? ';

            $this->sqlite->query($sql,$name,$author,$email,$date,$text,$image,$category,$id);
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
    public static function ShortName($name = "",$l = 25) {
        if(strlen($name) > $l){
            $name = mb_substr($name,0,$l - 3).'...';
        }
        return (string) $name;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @return array all stream from dir
     */
    public function AllStream() {
        $streams = array();
        $sql = 'SELECT s.name FROM '.self::db_table_stream.' s';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streams[] = $row['name'];
        }
        return $streams;
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param int $i
     * @return string
     */
    public static function _is_even($i) {
        return helper_plugin_fkshelper::_is_even($i);
    }

    /**
     * 
     * @param type $id
     * @return type
     */
    public function _generate_token($id) {
        $hash_no = (int) $this->getConf('hash_no');
        $l = (int) $this->getConf('no_pref');
        $pre = helper_plugin_fkshelper::_generate_rand($l);
        $pos = helper_plugin_fkshelper::_generate_rand($l);
        $hex = dechex($hash_no + 2 * $id);
        $hash = $pre.$hex.$pos;
        return (string) DOKU_URL.'?do=fksnewsfeed_token&token='.$hash;
    }

    /**
     * load news @i@ and return text
     * @author     Michal Červeňák <miso@fykos.cz>
     * @param int $id
     * @return array
     */
    public function LoadSimpleNews($id) {
        $sql = 'SELECT * FROM '.self::db_table_feed.' where news_id='.$id.'';
        $res = $this->sqlite->query($sql);
        foreach ($this->sqlite->res2arr($res) as $row) {

            return $row;
        }
    }

    public function all_values($field) {
        return $$this->AllValues($field);
    }

    /**
     * 
     * @param string $field name of field
     * @return array
     */
    public function AllValues($field) {
        $values = array();
        $sql = 'SELECT t.? FROM '.self::db_table_feed.' t GROUP BY t.?';
        $res = $this->sqlite->query($sql,$field,$field);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $values[] = $row[$field];
        }
        return $values;
    }

    /**
     * 
     * @param type $stream_id
     * @return array
     */
    public function AllParentDependence($stream_id) {

        $stream_ids = array();
        $sql = 'SELECT * FROM '.self::db_table_dependence.' t WHERE t.parent=?';
        $res = $this->sqlite->query($sql,$stream_id);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['child'];
        }
        return $stream_ids;
    }

    /**
     * 
     * @param int $stream_id 
     * @return array of stream ID
     */
    public function AllChildDependence($stream_id) {

        $stream_ids = array();
        $sql = 'SELECT * FROM '.self::db_table_dependence.' t WHERE t.child=?';
        $res = $this->sqlite->query($sql,$stream_id);
        foreach ($this->sqlite->res2arr($res) as $row) {

            $stream_ids[] = $row['parent'];
        }
        return $stream_ids;
    }

    public function create_dependence($stream_id,&$arr) {
        $this->FullParentDependence($stream_id,$arr);
    }

    /**
     * 
     * @param type $stream_id
     * @param type $arr
     */
    public function FullParentDependence($stream_id,&$arr) {
        foreach ($this->AllParentDependence($stream_id)as $new_stream_id) {
            if(!in_array($new_stream_id,$arr)){
                $arr[] = $new_stream_id;

                $this->FullParentDependence($new_stream_id,$arr);
            }
        }
    }

    /**
     * 
     * @param type $stream_id
     * @param type $arr
     */
    public function FullChildDependence($stream_id,&$arr) {

        foreach ($this->AllChildDependence($stream_id)as $new_stream_id) {
            if(!in_array($new_stream_id,$arr)){
                $arr[] = $new_stream_id;

                $this->FullChildDependence($new_stream_id,$arr);
            }
        }
    }

    public function save_to_stream($stream_id,$id,$weight = null) {
        return (int) $this->SaveIntoStream($stream_id,$id,$weight);
    }

    /**
     * 
     * @param type $stream_id
     * @param type $id
     * @param type $weight
     * @return type
     */
    public function SaveIntoStream($stream_id,$id) {
        

        $sql3 = 'INSERT INTO '.self::db_table_order.' (news_id,stream_id,priority) values(?,?,?)';
        $this->sqlite->query($sql3,$id,$stream_id,0);
        
        return (int) 1;
    }

    public function update_stream($weigth,$order_id) {
        return $this->UpdateWeight($weigth,$order_id);
    }

    /**
     * 
     */
    public function CleanOrder() {
        $sql = 'DELETE FROM '.self::db_table_order.' WHERE weight=0;';
        return $this->sqlite->query($sql);
    }

    /**
     * 
     * @param type $weigth
     * @param type $order_id
     * @return type
     */
    public function UpdateWeight($weigth,$order_id) {
        $sql = 'UPDATE '.self::db_table_order.' SET weight=? WHERE order_id=?';
        return $this->sqlite->query($sql,$weigth,$order_id);
    }

    public function create_order_div($news_id,$order_id,$weight,$k = 0) {
        $form = "";
        $e = $this->_is_even($k);
        $n = str_replace(array('@id@','@even@','@edited@'),array($news_id,$e,'false'),$this->simple_tpl);
        $form.= '<div class="simple_order_div" data-index="'.$order_id.'" data-id="'.$news_id.'">';
        $form.='<div class="delete_news">           
                <button type="button" class="close" >
  <span aria-hidden="true">&times;</span>
</button>';
        $form.='<span>'.$this->getLang('weight').'</span><input type="number" class="edit" name="weight['.$order_id.']" value="'.$weight.'">';
        $form.='</div>';
        $info = array();
        $form.=p_render("xhtml",p_get_instructions($n),$info);
        $form.='</div>';
        return $form;
    }

    /**
     * TODO!!!
     */
    public function CreateStream($stream_name) {
        $sql1 = 'INSERT INTO '.self::db_table_stream.' (name) VALUES(?);';
        $this->sqlite->query($sql1,$stream_name);
        $stream_id = $this->StreamToID($stream_name);
        return $stream_id;
    }

    /**
     * return name of stream.
     * @author Michal Cervenak <miso@fykos.cz>
     * 
     * @param int $id referent id of stream
     * @return string
     */
    public function IDtoStream($id) {
        $sql1 = 'SELECT name FROM '.self::db_table_stream.' where stream_id=?';
        $res1 = $this->sqlite->query($sql1,$id);
        $stream_name = $this->sqlite->res2single($res1);
        if($stream_name == 0){
            $this->errors[] = _('Stream dont\' exist');
        }
        return (string) $stream_name;
    }

    /**
     * Create dependence betwen parent and child stream 
     * @author Michal Cervenak <miso@fykos.cz>
     * 
     * @param int $parent id of parent stream
     * @param int $child id of child stream
     * @return boolean 
     */
    public function CreateDependence($parent,$child) {
        $sql1 = 'insert into '.self::db_table_dependence.' (parent,child) VALUES(?,?);';
        $r = $this->sqlite->query($sql1,$parent,$child);
        return (bool) $r;
    }

    public function SavePriority($news_id,$stream_id,$p,$from,$to) {
        $sql = 'UPDATE '.self::db_table_order.' SET priority=?,priority_from=?,priority_to=? WHERE stream_id=? AND news_id =?';
        return $this->sqlite->query($sql,$p,$from,$to,$stream_id,$news_id);
    }

    public function FindPriority($news_id,$stream_id) {
        $sql = 'SELECT * FROM '.self::db_table_order.' WHERE stream_id=? AND news_id =?';
        $res = $this->sqlite->query($sql,$stream_id,$news_id);
        
        return $this->sqlite->res2arr($res);
    }

}

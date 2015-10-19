<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if(!defined('DOKU_INC')){
    die();
}
if(!defined('DOKU_PLUGIN')){
    define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
}
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed\>.+?\}\}',$mode,'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $text = str_replace(array("\n",'{{fksnewsfeed>','}}'),array('','',''),$match);
        /** @var id and even this NF $param */
        $param = $this->helper->FKS_helper->ExtractParamtext($text);
        return array($state,array($param));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        // $data is what the function handle return'ed.
        if($mode == 'xhtml'){
            $r = "";
            /** @var Do ku_Renderer_xhtml $renderer */
            list(,list($param) ) = $data;
            /*
             * if not valid ID
             */
            if($param['id'] == 0){
                $r.= '<div class="FKS_newsfeed_exist_msg">'.$this->getLang('news_non_exist').'</div>';
                return;
            }
            /*
             * if no data gived
             */
            if(empty($data)){
                $r.= '<div class="FKS_newsfeed_exist_msg">'.$this->getLang('news_non_exist').'</div>';
                return;
            }
            /*
             * FR anther tpl for streams
             */
            $param['tpl'] = 'default';
            $f = $this->helper->getCasheFile($param['id'],$param['tpl']);
            $cache = new cache($f,'');


            /* can I use cache?
             */
            if($cache->useCache()){

                $tpl = $cache->retrieveCache();   //load tpl without edit section
            }else{
                $data = $this->helper->LoadSimpleNews($param["id"]);
                $tpl_path = wikiFN($this->getConf('tpl'));
                if(!file_exists($tpl_path)){
                    $def_tpl = DOKU_PLUGIN.plugin_directory('fksnewsfeed').'/tpl.html';
                    io_saveFile($tpl_path,io_readFile($def_tpl));
                }
                $tpl = io_readFile($tpl_path);

                $img_attr = array();

                foreach (helper_plugin_fksnewsfeed::$Fields as $k) {
                    /*
                     * render image 
                     */
                    if($k == 'image'){
                        if($data['image'] != ""){
                            $div_class.=' w_image';
                            $data['image'] = '<div class="image"><img src="'.ml($data['image'],array('w' => 300)).'" alt="newsfeed" '.buildAttributes($img_attr).'></div>';
                            $tpl = str_replace('@'.$k.'@',$data[$k],$tpl);
                        }else{
                            $tpl = str_replace('@'.$k.'@','',$tpl);
                            continue;
                        }
                    }/*
                     * render dokutext
                     */
                    if($k == 'text'){
                        $info = array();
                        $data['text'] = p_render('xhtml',p_get_instructions($data['text']),$info);
                        $tpl = str_replace('@'.$k.'@',$data[$k],$tpl);
                        continue;
                    }
                    /*
                     * ecsape others params
                     */
                    $data[$k] = htmlspecialchars($data[$k]);

                    /*
                     * create date
                     */
                    if($k == 'newsdate'){

                        $data['newsdate'] = $this->newsdate($data['newsdate']);
                    }
                    /*
                     * add to template
                     */
                    $tpl = str_replace('@'.$k.'@',$data[$k],$tpl);
                }
                $cache->storeCache($tpl);
            }

            if($param['edited'] === 'true'){
                list($r1,$ar1) = $this->BtnEditNews($param["id"],$param['stream']);
                list($r2,$ar2) = $this->getPriorityField($param["id"],$param['stream']);

                $edit = '<div class="edit" data-id="'.$param["id"].'">'.$r1.$r2.'</div>';
                $edit .= '<div class="edit_field" data-id="'.$param["id"].'">'.$ar1.$ar2.'</div>';
            }else{
                $edit = '';
            }

            $tpl = str_replace('@edit@',$edit,$tpl);


            /*
             * empty category ist default!!!
             */

            if($data['category'] == ""){
                $data['category'] = 'default';
            }else{
                $data['category'] = htmlspecialchars($data['category']);
            }

            /*
             * default is even
             */
            if(!isset($param['even'])){
                $param['even'] = 'even';
            }
            $div_class = $param['even'].' '.$data['category'];

            $r.= '<div class="'.$div_class.'" data-id="'.$param["id"].'">'.$tpl.'</div>';

            $renderer->doc.=$r;
        }
        return false;
    }

    private function getPriorityField($id,$stream) {
        $r = '';
        $ar = '';
        if(auth_quickaclcheck('start') >= AUTH_EDIT){

            $r.='<div class="priority_btn">';
            $r.='<button class="button priority_btn">'.$this->getLang('btn_priority_edit').'</button>';
            $r.='</div>';

            $ar.='<div class="priority">';

            $form2 = new Doku_Form(array());
            $form2->addHidden("do","show");
            $form2->addHidden('news_id',$id);
            $form2->addHidden('news_stream',$stream);
            $form2->addHidden('news_do','priority');
            $form2->addHidden("target","plugin_fksnewsfeed");

            $stream_id = $this->helper->StreamToID($stream);
            list($p) = $this->helper->FindPriority($id,$stream_id);



            $form2->addElement(form_makeField('number','priority',$p['priority'],$this->getLang('priority_value'),null,'block',array('step' => 1)));
            $form2->addElement(form2_makeDateTimeField('priority_form',$p['priority_from'],$this->getLang('valid_from'),null,'block',1,1,array()));
            $form2->addElement(form2_makeDateTimeField('priority_to',$p['priority_to'],$this->getLang('valid_to'),null,'block',1,1,array()));


            $form2->addElement(form_makeButton('submit','',$this->getLang('btn_save_priority')));

            ob_start();
            html_form('editnews',$form2);

            $ar.= ob_get_contents();
            ob_clean();

            $ar.='</div>';
        }

        return array($r,$ar);
    }

    private function BtnEditNews($id,$stream) {
        $r = '';
        $ar = '';

        if(auth_quickaclcheck('start') >= AUTH_EDIT){
            $form = new Doku_Form(array('id' => 'editnews','method' => 'POST','class' => 'fksreturn'));
            $form->addHidden("do","edit");
            $form->addHidden('news_id',$id);
            $form->addHidden("target","plugin_fksnewsfeed");
            $form->addElement(form_makeButton('submit','',$this->getLang('btn_edit_news')));

            ob_start();
            html_form('editnews',$form);

            $r.='<div class="edit_btn">';
            $r.= ob_get_contents();
            ob_clean();
            $r.='</div>';
        }
        if(auth_quickaclcheck('start') >= $this->getConf('perm_fb')){
            $r.= '<div class="share_btn"><button data-href="'.$this->helper->_generate_token((int) $id).'"'.
                    ' class="button fb-btn">';
            $r.= $this->getLang('btn_share_fb').'</button>';
            $r.='</div>';
        }
        if(auth_quickaclcheck('start') >= $this->getConf('perm_link')){
            $r.='<div class="link_btn">';
            $r.=html_button($this->getLang('btn_newsfeed_link'),'button link_btn',array('data-id' => $id));
            $link = $this->helper->_generate_token((int) $id);
            $r.='</div>';

            $ar.='<div class="link">';

            $ar.='<span contenteditable="true" class="link_inp"  data-id="'.$id.'">'.$link.'</span>';

            $ar.='</div>';
        }
        if(auth_quickaclcheck('start') >= AUTH_EDIT){
            ob_start();
            $form = new Doku_Form(array());

            $form->addHidden('news_do','delete_save');
            $form->addHidden('target','plugin_fksnewsfeed');
            $form->addHidden('stream',$stream);
            $form->addHidden('news_id',$id);

            $form->addElement(form_makeButton('submit',null,'Ostrániť z vlákna',array('id' => 'warning')));


            html_form('editnews',$form);



            $r.='<div class="delete_btn">';
            $r.= ob_get_contents();
            ob_clean();
            $r.='</div>';
        }

        return array($r,$ar);
    }

    private function newsdate($date) {

        $date = date('d\. F Y',strtotime($date));
        $enmonth = array(
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
            'December'
        );
        $langmonth = array(
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
        return (string) str_replace($enmonth,$langmonth,$date);
    }

}

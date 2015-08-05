<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if(!defined('DOKU_INC')){
    die;
}

/** $INPUT 
 * @news_do add/edit/
 * @news_id no news
 * @news_strem name of stream
 * @id news with path same as doku @ID
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */
class action_plugin_fksnewsfeed_ajax extends DokuWiki_Action_Plugin {

    private $helper;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     * 
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {


        $controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'ajax_stream');
        $controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'ajax_more');
        //$controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'ajax_edit');
        $controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'ajax_add');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function ajax_stream(Doku_Event &$event,$param) {
        global $INPUT;

        if($INPUT->str('target') != 'feed'){
            return;
        }

        require_once DOKU_INC.'inc/JSON.php';
        header('Content-Type: application/json');
        if($INPUT->str('news_do') == 'stream'){

            $event->stopPropagation();
            $event->preventDefault();
            

            $feed = (int) $INPUT->str('news_feed');
            $r = (string) "";


            if(auth_quickaclcheck('start') >= $this->getConf('perm_manage')){
                $r.=$this->_get_manage_btn($INPUT->str('news_stream'));
            }

            if(auth_quickaclcheck('start') >= $this->getConf('perm_rss')){


                $r.=html_open_tag('div',array('class' => 'form-group FKS_newsfeed_rss'));
                $r.=html_open_tag('div',array('class' => 'input-group'));
                $r.=html_open_tag('span',array('class' => 'input-group-addon'));
                $r.='RSS'.html_close_tag('span');
                $r.=html_make_tag('input',array(
                    'class' => 'form-control',
                    'data-id' => 'rss',
                    'type' => 'text',
                    'value' => DOKU_URL.'feed.php?stream='.$INPUT->str('news_stream')));
                $r.=html_close_tag('div').html_close_tag('div');
            }
            $news=$this->helper->LoadStream($INPUT->str('news_stream'),true);
            foreach ($news as $key => $value) {
                $id = $value['news_id'];
                if($feed){
                    $e = $this->helper->_is_even($key);
                    $n = str_replace(array('@id@','@even@'),array($id,$e),$this->helper->simple_tpl);
                    $r.= p_render("xhtml",p_get_instructions($n),$info);

                    $feed --;
                }else{
                    break;
                }
            }
            $r.=$this->_add_button_more($INPUT->str('news_stream'),$INPUT->str('news_feed'));
            $json = new JSON();
            $er="";
            foreach ($this->helper->errors as $erro){
               $er.='<div class="error">'.$erro.'</div>';
               
            }
            unset ($this->helper->errors);
            echo $json->encode(array("r" => $er.$r));
        }else{
            return;
        }
    }

    public function ajax_more(Doku_Event &$event,$param) {
        global $INPUT;
        if($INPUT->str('target') != 'feed'){
            return;
        }
        require_once DOKU_INC.'inc/JSON.php';
        header('Content-Type: application/json');
        if($INPUT->str('news_do') == 'more'){
            $event->stopPropagation();
            $event->preventDefault();

            $f = $this->helper->loadstream($INPUT->str('news_stream'));
            (int) $max = (int) $this->getConf('more_news') + (int) $INPUT->str('news_view');
            $more = false;
            for ($i = (int) $INPUT->str('news_view'); $i < $max; $i++) {
                if(array_key_exists($i,$f)){
                    $e = $this->helper->_is_even($i);

                    $n = str_replace(array('@id@','@even@'),array($f[$i]['news_id'],$e),$this->helper->simple_tpl);
                    $r.= p_render("xhtml",p_get_instructions($n),$info);
                }else{
                    $more = true;
                    $r.= html_open_tag('div',array('class' => 'FKS_newsfeed_more_msg'));
                    $r.=$this->getLang('no_more');
                    $r.=html_close_tag('div');
                    break;
                }
            }
            $r.= $this->_add_button_more($INPUT->str('news_stream'),$max);
            $json = new JSON();
            echo $json->encode(array('news' => $r,'more' => $more));
        }else{
            return;
        }
    }

    

    public function ajax_add(Doku_Event &$event,$param) {
        global $INPUT;

        if($INPUT->str('target') != 'feed'){
            return;
        }
        require_once DOKU_INC.'inc/JSON.php';
        header('Content-Type: application/json');

        if($INPUT->str('news_do') == 'add'){
            $event->stopPropagation();
            $event->preventDefault();
            $weight = $INPUT->str('news_weight');
            $news_id = $INPUT->str('news_id');
            $stream_id = $this->helper->StreamToID($INPUT->str('news_stream'));
            $order_id = $this->helper->SaveIntoStream($stream_id,$news_id,0);
            $r['order_div'] = $this->helper->create_order_div($news_id,$order_id,$weight);
        }
        $json = new JSON();

        echo $json->encode($r);
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $stream
     * @param int $more
     * @return string
     */
    private function _add_button_more($stream,$more) {

        return html_open_tag('div',array(
                    'class' => 'more_news',
                    'data-stream' => (string) $stream,
                    'data-view' => (int) $more)).
                html_button($this->getLang('btn_more_news'),'button',array('title' => 'fksnewsfeed'))
                .html_close_tag('div');
    }

    private function _get_manage_btn($stream) {
        $form2 = new Doku_Form(array('id' => 'addnews','method' => 'GET','class' => 'fksreturn'));
        $form2->addHidden('target','plugin_fksnewsfeed');
        $form2->addHidden('news_do','order');
        $form2->addHidden('news_stream',$stream);
        $form2->addElement(form_makeButton('submit','',$this->getLang('btn_manage_stream')));
        ob_start();
        html_form('addnews',$form2);
        $r = ob_get_contents();
        ob_end_clean();
        return $r;
    }

}

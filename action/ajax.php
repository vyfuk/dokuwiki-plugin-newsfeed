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


        $controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'Stream');
        $controller->register_hook('AJAX_CALL_UNKNOWN','BEFORE',$this,'WeightAdd');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function Stream(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str('target') != 'feed'){
            return;
        }
        require_once DOKU_INC.'inc/JSON.php';

        if($INPUT->str('news_do') == 'stream' || $INPUT->str('news_do') == 'more'){
            header('Content-Type: application/json');
            $event->stopPropagation();
            $event->preventDefault();

            $r = (string) "";
            if($INPUT->str('news_do') == 'stream'){
                if(auth_quickaclcheck('start') >= $this->getConf('perm_manage')){
                    $this->PrintCreateBtn($r,$INPUT->str('news_stream'));
                    $this->PrintManageBtn($r,$INPUT->str('news_stream'));
                }
                if(auth_quickaclcheck('start') >= $this->getConf('perm_rss')){
                    $this->PrintRSS($r,$INPUT->str('news_stream'));
                }
            }
            $news = $this->helper->LoadStream($INPUT->str('news_stream'),true);
            $more = $this->PrintStream($news,$r,(int) $INPUT->str('news_feed_s',0),(int) $INPUT->str('news_feed_l',3));
            $json = new JSON();
            $er = "";
            foreach ($this->helper->errors as $erro) {
                $er.='<div class="error">'.$erro.'</div>';
            }
            unset($this->helper->errors);
            echo $json->encode(array('more' => $more,"r" => $er.$r));
        }else{
            return;
        }
    }

    public function PrintStream($news,&$r,$s = 0,$l = 5) {
        global $INPUT;
        $more = false;
        for ($i = $s; $i < min(array($s + $l,(count($news)))); $i++) {
            $e = $this->helper->_is_even($i);
            $r.=$this->PrintNews($news[$i]['news_id'],$e);
        }
        if($l + $s >= count($news)){
            $more = true;
            $r.= html_open_tag('div',array('class' => 'msg'));
            $r.=$this->getLang('no_more');
            $r.=html_close_tag('div');
        }else{
            $r.=$this->PrintMoreBtn($INPUT->str('news_stream'),$l + $s);
        }
        return $more;
    }

    public function PrintNews($id,$e) {
        $n = str_replace(array('@id@','@even@'),array($id,$e),$this->helper->simple_tpl);
        $info = array();
        return p_render("xhtml",p_get_instructions($n),$info);
    }

    public function WeightAdd(Doku_Event &$event) {
        global $INPUT;
        if($INPUT->str('target') != 'feed'){
            return;
        }
        require_once DOKU_INC.'inc/JSON.php';
        header('Content-Type: application/json');
        if($INPUT->str('news_do') == 'weight_add'){
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
    private function PrintMoreBtn($stream,$more) {

        return html_open_tag('div',array(
                    'class' => 'more_news',
                    'data-stream' => (string) $stream,
                    'data-view' => (int) $more)).
                html_button($this->getLang('btn_more_news'),'button',array('title' => 'fksnewsfeed'))
                .html_close_tag('div');
    }

    /**
     * 
     * @param type $r
     * @param type $stream
     */
    private function PrintManageBtn(&$r,$stream) {
        $form2 = new Doku_Form(array('id' => 'addnews','method' => 'GET','class' => 'fksreturn'));
        $form2->addHidden('target','plugin_fksnewsfeed');
        $form2->addHidden('news_do','order');
        $form2->addHidden('news_stream',$stream);
        $form2->addElement(form_makeButton('submit','',$this->getLang('btn_manage_stream')));
        ob_start();
        html_form('addnews',$form2);
        $r .= ob_get_contents();
        ob_end_clean();
    }

    /**
     * 
     * @param type $r
     * @param type $stream
     */
    private function PrintRSS(&$r,$stream) {
        $r.=html_open_tag('div',array('class' => 'rss'));
        $r.=html_open_tag('div');
        $r.=html_open_tag('span',array());
        $r.='RSS'.html_close_tag('span');
        $r.=html_close_tag('div');
        $r.=html_make_tag('input',array(
            'data-id' => 'rss',
            'type' => 'text',
            'value' => DOKU_URL.'feed.php?stream='.$stream));
        $r.=html_close_tag('div');
    }

    private function PrintCreateBtn(&$r,$stream) {
        $r.='<h2 id="menu_create_news">'.$this->getLang('menu_create_news').'</h2>';
        $r.'<p>'.$this->getLang('info_create_news').'</p>';

        $form3 = new Doku_Form(array('method' => 'GET'));
        $form3->addHidden('do','edit');
        $form3->addHidden('target','plugin_fksnewsfeed');
        $form3->addHidden('news_do','create');
        $form3->addHidden('news_id',0);
        $form3->addHidden('news_stream',$stream);
        $form3->addElement(form_makeButton('submit','',$this->getLang('btn_create_news')));

        ob_start();
        html_form('create_news',$form3);
        $r.=ob_get_contents();
        ob_clean();
    }

}

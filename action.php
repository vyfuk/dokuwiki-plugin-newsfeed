<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if (!defined('DOKU_INC')) {
    die();
}

/** $INPUT 
 * @news_do add/edit/
 * @news_id no news
 * @news_strem name of stream
 * @id news with path
 * @news_feed how many newsfeed need display
 * @news_view how many news is display
 */

class action_plugin_fksnewsfeed extends DokuWiki_Action_Plugin {

    private $hash = array('pre' => null, 'pos' => null, 'hex' => null, 'hash' => null);
    private $modFields = array('name', 'email', 'author', 'newsdate', 'text');
    private $helper;
    private $token = array('show' => false, 'id' => null);

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'enc_tocen');
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_html_edit_formselection');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_action_ajax_request');
    }

    public function enc_tocen(Doku_Event &$event, $param) {


        if ($this->token['show']) {
            $e = $this->helper->_is_even($this->token['id']);

            $event->preventDefault();

            echo p_render('xhtml', p_get_instructions(str_replace(array('@id@', '@even@'), array($this->token['id'], $e), $this->helper->simple_tpl)), $info);
        }
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_action_ajax_request(Doku_Event &$event, $param) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }

        $event->stopPropagation();
        $event->preventDefault();
        require_once DOKU_INC . 'inc/JSON.php';
        header('Content-Type: application/json');
        if ($INPUT->str('news_do') == 'edit') {
            $r = '';

            if ($_SERVER['REMOTE_USER']) {
                $form = new Doku_Form(array('id' => 'editnews', 'method' => 'POST', 'class' => 'fksreturn'));
                $form->addHidden("do", "edit");
                $form->addHidden('id', $this->helper->getwikinewsurl($INPUT->str('news_id')));
                $form->addHidden("target", "plugin_fksnewsfeed");
                $form->addElement(form_makeButton('submit', '', $this->getLang('subeditnews')));
                ob_start();

                html_form('editnews', $form);
                $r.='<div class="secedit">';
                $r.= ob_get_contents();
                $r.='</div>';
                ob_end_clean();
            }


            if ($this->getConf('facebook_allow') || ($this->getConf('facebook_allow_user') && $_SERVER['REMOTE_USER'])) {
                $fb_class='fb-share-button btn btn-small btn-social btn-facebook';
                $fb_atr=array('data-href' => $this->_generate_token((int) $INPUT->str('news_id')));
                $r.= html_facebook_btn('Share on FB', $fb_class,$fb_atr );
            }
            if ($this->getConf('token_allow') || ($this->getConf('token_allow_user') && $_SERVER['REMOTE_USER'])) {
                $r.=html_button($this->getLang('newsfeed_link'), 'btn btn-info FKS_newsfeed_button FKS_newsfeed_link_btn', array('data-id' => $INPUT->str('news_id')));
                $link = $_SERVER['SERVER_NAME'] . DOKU_BASE . '?do=fksnewsfeed_token&token=' . $this->_generate_token((int) $INPUT->str('news_id'));
                $r.='<input class="FKS_newsfeed_link_inp" data-id="' . $INPUT->str('news_id') . '" style="display:none" type="text" value="' . $link . '" />';
            }



            $json = new JSON();

            echo $json->encode(array("r" => $r));
        } elseif ($INPUT->str('news_do') == 'stream') {
            $feed = (int) $INPUT->str('news_feed');
            $r = (string) "";
            if ($_SERVER['REMOTE_USER']) {
                $form = new Doku_Form(array('id' => 'addnews', 'method' => 'GET', 'class' => 'fksreturn'));
                $form->addHidden("do", "edit");

                $form->addHidden("target", "plugin_fksnewsfeed");
                $form->addHidden('news_do', 'add');
                $form->addHidden('news_id', $this->helper->findimax('feeds'));
                $form->addHidden('id', $this->helper->getwikinewsurl($this->helper->findimax('feeds')));
                $form->addHidden("news_stream", $INPUT->str('news_stream'));
                $form->addElement(form_makeButton('submit', '', $this->getLang('subaddnews')));
                ob_start();
                html_form('addnews', $form);
                $r .= ob_get_contents();
                ob_end_clean();
            }

            foreach ($this->helper->loadstream($INPUT->str('news_stream'), true) as $key => $value) {
                if ($feed) {
                    $e = $this->helper->_is_even($key);

                    $n = str_replace(array('@id@', '@even@'), array($value, $e), $this->helper->simple_tpl);
                    $r.= p_render("xhtml", p_get_instructions($n), $info);

                    $feed --;
                } else {
                    break;
                }
            }
            $r.=$this->_add_button_more($INPUT->str('news_stream'), $INPUT->str('news_feed'));

            $json = new JSON();

//echo $r;
            echo $json->encode(array("r" => $r));
        } elseif ($INPUT->str('news_do') == 'more') {
            $f = $this->helper->loadstream($INPUT->str('news_stream'));
            (int) $max = (int) $this->getConf('more_news') + (int) $INPUT->str('news_view');
            for ($i = (int) $INPUT->str('news_view'); $i < $max; $i++) {
                if (array_key_exists($i, $f)) {
                    $e = $this->helper->_is_even($i);

                    $n = str_replace(array('@id@', '@even@'), array($f[$i], $e), $this->helper->simple_tpl);
                    $r.= p_render("xhtml", p_get_instructions($n), $info);
                } else {
                    break;
                }
            }
            $r.= $this->_add_button_more($INPUT->str('news_stream'), $max);
            $json = new JSON();

            echo $json->encode(array("r" => $r));
        } else {
            return;
        }
    }

    public function handle_html_edit_formselection(Doku_Event &$event, $param) {
        global $TEXT;
        global $INPUT;
        global $ID;
        if ($INPUT->str('target') !== 'plugin_fksnewsfeed') {
            return;
        }
        $event->preventDefault();
        unset($event->data['intro_locale']);
        echo $this->locale_xhtml('edit_intro');
        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->modFields as $field) {
                $data[$field] = $INPUT->param($field);
            }
        } else {
            $news_path = $INPUT->str("id");
            $data = $this->extractParamACT(io_readFile(metaFN($news_path, ".txt")));
        }

        $form->startFieldset('Newsfeed');
        $form->addHidden('id', $news_path);
        $form->addHidden('target', 'plugin_fksnewsfeed');
        foreach ($this->modFields as $field) {
            if ($field == 'text') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                $form->addElement(form_makeWikiText($TEXT, array()));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, array()));
            }
        }
        $form->endFieldset();
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {
        global $ACT;
        global $INPUT;


        if ($INPUT->str("target") == "plugin_fksnewsfeed") {
            global $INPUT;
            global $TEXT;
            global $ID;
            global $INFO;
            global $ACT;

//$this->helper->_log_event('edit', $INPUT->str('id'));
            if ($INPUT->str('news_do') == 'add') {
                //var_dump($INPUT);

                $Wnews = $this->helper->saveNewNews(array('author' => $INFO['userinfo']['name'],
                    'newsdate' => dformat(),
                    'email' => $INFO['userinfo']['mail'],
                    'text' => 'Tady napiš text aktuality',
                    'name' => 'Název aktuality'), $this->helper->getwikinewsurl($INPUT->str('news_id')));
                if ($Wnews) {
                    $c = '';
                    $c.=';' . $INPUT->str('news_id') . ";";
                    $c.=io_readFile(metaFN('fksnewsfeed/streams/' . $INPUT->str('news_stream'), ".csv"), FALSE);
                    if (io_saveFile(metaFN('fksnewsfeed/streams/' . $INPUT->str('news_stream'), ".csv"), $c)) {
                        msg(' written successful', 1);
                    } else {
                        msg("written failure", -1);
                    }
                } else {
                    msg("written into new news failure", -1);
                }
            }
            if (isset($_POST['do']['save'])) {

                $data = array();
                foreach ($this->modFields as $field) {
                    if ($field == 'text') {
                        $data[$field] = cleanText($INPUT->str('wikitext'));
                        unset($_POST['wikitext']);
                    } else {
                        $data[$field] = $INPUT->param($field);
                    }
                }
                $this->helper->saveNewNews($data, $INPUT->str('id'), true);
                unset($TEXT);
                unset($_POST['wikitext']);
                $ACT = "show";
                $ID = 'start';
            }
        } elseif ($ACT == 'fksnewsfeed_token') {
            $token = $INPUT->str('token');
            $this->token['id'] = $id = $this->_encript_hash($token, $this->getConf('no_pref'), $this->getConf('hash_no'));
            $this->token['show'] = true;
//$ACT = 'show';
        }
    }

    private function extractParamACT($ntext) {
        global $TEXT;
        $cleantext = str_replace(array("\n", '<fksnewsfeed', '</fksnewsfeed>'), array('', '', ''), $ntext);
        list($params, $text) = preg_split('/\>/', $cleantext, 2);
        $param = $this->helper->FKS_helper->extractParamtext($params);
        $TEXT = $text;
        return $param;
    }

    private function _add_button_more($stream, $more) {

        return '<div class="FKS_newsfeed_more" data-stream="' . (string) $stream . '" data-view="' . (int) $more . '">' .
                html_button($this->getLang('old_news'), 'button', array('title' => 'fksnewsfeed'))
                . '</div>';
    }

    private function _generate_token($id) {


        $hash_no = (int) $this->getConf('hash_no');
        $l = (int) $this->getConf('no_pref');


        $this->hash['pre'] = $this->_generate_rand($l);
        $this->hash['pos'] = $this->_generate_rand($l);
        $this->hash['hex'] = dechex($hash_no + 2 * $id);

        $this->hash['hash'] = $this->hash['pre'] . $this->hash['hex'] . $this->hash['pos'];

        return $this->hash['hash'];
    }

    private function _generate_rand($l) {

        $r = '';
        $seed = str_split('1234567890abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'); // and any other characters
        shuffle($seed);
        foreach (array_rand($seed, $l) as $k) {
            $r .= $seed[$k];
        }
        return $r;
    }

    private function _encript_hash($hash, $l, $hash_no) {
        $enc_hex = substr($hash, $l, -$l);

        $enc_dec = hexdec($enc_hex);

        $id = ($enc_dec - $hash_no) / 2;
        return $id;
    }

}

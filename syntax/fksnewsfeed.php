<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN')) {
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {

    private $helper;
    private $social;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
        $this->social = $this->helper->social;
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed\>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state) {
        $text = str_replace(array("\n", '{{fksnewsfeed>', '}}'), array('', '', ''), $match);
        /** @var id and even this NF $param */
        $param = $this->helper->FKS_helper->ExtractParamtext($text);

        return array($state, array($param));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list(, list($param)) = $data;


            $data = $this->helper->LoadSimpleNews($param["id"]);
            if (empty($data) || ($param['id'] == 0)) {
                $renderer->doc .= '<div class="error">' . $this->getLang('news_non_exist') . '</div>';
                return;
            }
            $tpl = $this->CreateTpl();
            require_once DOKU_INC . 'inc/JSON.php';

            if (!isset($param['even'])) {
                $param['even'] = 'even';
            }
            $div_class = $param['even'];
            $f = $this->helper->getCacheFile($param['id']);
            $cache = new cache($f, '');
            $json = new JSON();
            if ($cache->useCache()) {
                list($c, $div_class_ap) = $json->decode($cache->retrieveCache());
            } else {
                $r = $this->CreateNews($data, $div_class);
                $cache->storeCache($json->encode($r));
                list($c, $div_class_ap) = $r;
            }
            $div_class .= ' ' . $div_class_ap;
            $c = (array)$c;

            foreach ($this->helper->Fields as $k) {
                $tpl = str_replace('@' . $k . '@', $c[$k], $tpl);
            }
            $page_id = $param['pageID'];

            $tpl = str_replace('@edit@', $this->CreateEditField($param, $c, $page_id), $tpl);


            $renderer->doc .= '<div class="' . $div_class . '"  data-id="' . $param["id"] . '">' . $tpl . '</div>';
        }
        return false;
    }

    private function CreateShareFields($id, $stream, $c, $page_id = "") {
        $html = "";


        $link = $this->helper->_generate_token((int)$id, $page_id);

        if (auth_quickaclcheck('start') >= AUTH_READ) {
            // $html.= '<button class="button share_btn">';
            //   $html.= '<span class="btn-small share-icon icon"></span>';
            //   $html.= '<span class="btn-big">'.$this->getLang('btn_share').'</span>';
            //   $html.= '</button>';

            $html .= '<div class="feed-social-container">';

            $html .= '<div class="Twitt">';
            $html .= '<a href="https://twitter.com/share" data-count="none" data-text="' . $c['name'] . '" class="twitter-share-button" data-url="' . $link . '" data-via="fykosak" data-hashtags="FYKOS">Tweet</a>';
            $html .= '</div>';


            $html .= '<div class="FB-msg">';
            $html .= $this->social->facebook->CreateSend($link);
            $html .= '</div>';

            $html .= '<div class="FB-share">';
            $html .= $this->social->facebook->CreateShare($link);
            $html .= '</div>';

            $html .= '<div class="whatsapp-share">';
            $html .= $this->social->whatsapp->CreateSend($link);
            $html .= '</div>';


            $html .= '<div class="link">';
            $html .= '<span class="link-icon icon"></span>';
            $html .= '<span contenteditable="true" class="link_inp" >' . $link . '</span>';
            $html .= '</div>';

            $html .= '</div>';
        }
        return array($html);
    }

    private function CreateNews($data) {
        $div_class = "";
        foreach ($this->helper->Fields as $k) {
            if ($k == 'image') {
                if ($data['image'] != "") {
                    $div_class .= ' w_image';
                    $c['image'] = '<div class="image"><div class="image_content"><img src="' . ml($data['image'], array('w' => 300)) . '" alt="newsfeed"></div></div>';
                } else {
                    $c['image'] = '';
                }
                continue;
            }
            if ($k == 'text') {
                $info = array();
                $c['text'] = p_render('xhtml', p_get_instructions($data['text']), $info);

                continue;
            }
            $c[$k] = htmlspecialchars($data[$k]);
            if ($k == 'category') {
                if ($data['category'] == "") {
                    $c['category'] = 'default';
                }
                $div_class .= ' ' . $c['category'];
            }
            if ($k == 'newsdate') {
                $c['newsdate'] = $this->newsdate($data['newsdate']);
            }
        }
        return array($c, $div_class);
    }

    private function CreateEditField($param, $c, $page_id = "") {

        $html = "";


        list($r3, $ar3) = $this->CreateShareFields($param["id"], $param['stream'], $c, $page_id);
        $html .= $r3;
        if (auth_quickaclcheck('start') >= AUTH_EDIT) {
            $headline = "";
            $headline .= '<div class="edit-headline">';
         //   $headline .= '<span class="opt-icon icon"></span>';
            $headline .= '<span >' . $this->getLang('btn_opt') . '</span>';
            $headline .= '</div>';
            $ar1 = $this->BtnEditNews($param["id"], $param['stream'], $c);


            //$html .= '<div class="feed-priority-container">' . $r2 . $ar2 . '</div>';
            $html .= '<div class="feed-edit-container">' . $headline . $ar1 . '</div>';
        }

        return $html;

    }


    private function getPriorityField($id, $stream) {
        $content = '';

        $content .= '<div class="priority-body">';
        $form = new \dokuwiki\Form\Form();
        $form->setHiddenField('do', 'show');
        $form->setHiddenField('news_id', $id);
        $form->setHiddenField('news_stream', $stream);
        $form->setHiddenField('news_do', 'priority');
        $form->setHiddenField("target", "plugin_fksnewsfeed");

        $stream_id = $this->helper->StreamToID($stream);
        list($p) = $this->helper->FindPriority($id, $stream_id);

        $form->addFieldsetOpen($this->getLang('btn_priority_edit'));
        $form->addTextInput('priority', $this->getLang('priority_value'))->attr('pattern', '[0-9]+')->val($p['priority']);
        $priorityForm = new \dokuwiki\Form\InputElement('datetime-local', 'priority_form', $this->getLang('valid_from'));
        $priorityForm->val($p['priority_from']);
        $priorityTo = new \dokuwiki\Form\InputElement('datetime-local', 'priority_to', $this->getLang('valid_to'));
        $priorityTo->val($p['priority_to']);
        $form->addElement($priorityForm);
        $form->addElement($priorityTo);
        $form->addButton('submit', $this->getLang('save'));
        $form->addFieldsetClose();
        $content .= $form->toHTML();

        $content .= '</div>';


        return $content;
    }


    private function BtnEditNews($id, $stream) {
        $ar = '';
        $ar .= '<div class="edit-body">';
        $ar .= $this->getPriorityField($id, $stream);


        ob_start();
        $form = new Doku_Form(array('class' => 'info'));
        $form->addHidden("do", "edit");
        $form->addHidden('news_id', $id);
        $form->addHidden('news_do', 'edit');
        $form->addHidden("target", "plugin_fksnewsfeed");
        $form->addElement(form_makeButton('submit', '', $this->getLang('btn_edit_news')));

        html_form('', $form);
        $ar .= ob_get_contents();
        ob_clean();


        ob_start();
        $form2 = new Doku_Form(array('class' => 'danger'));
        $form2->addHidden('news_do', 'delete_save');
        $form2->addHidden('target', 'plugin_fksnewsfeed');
        $form2->addHidden('stream', $stream);
        $form2->addHidden('news_id', $id);
        $form2->addElement(form_makeButton('submit', null, $this->getLang('delete_news'), array('id' => 'warning')));
        html_form('editnews', $form2);
        $ar .= ob_get_contents();
        ob_clean();


        ob_start();
        $form3 = new Doku_Form(array('class' => 'warning'));
        $form3->addHidden('fksnewsfeed_purge', 'true');
        $form3->addHidden('news_id', $id);
        $form3->addElement(form_makeButton('submit', null, $this->getLang('cache_del')));
        html_form('cachenews', $form3);
        $ar .= ob_get_contents();
        ob_clean();
        $ar .= '</div>';


        return $ar;
    }

    private function newsdate($date) {

        $date = date('j\. F Y', strtotime($date));
        $enmonth = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $langmonth = array($this->getLang('jan'), $this->getLang('feb'), $this->getLang('mar'), $this->getLang('apr'), $this->getLang('may'), $this->getLang('jun'), $this->getLang('jul'), $this->getLang('aug'), $this->getLang('sep'), $this->getLang('oct'), $this->getLang('now'), $this->getLang('dec'));
        return (string)str_replace($enmonth, $langmonth, $date);
    }

    private function CreateTpl() {
        $tpl_path = wikiFN($this->getConf('tpl'));
        if (!file_exists($tpl_path)) {
            $def_tpl = DOKU_PLUGIN . plugin_directory('fksnewsfeed') . '/tpl.html';
            io_saveFile($tpl_path, io_readFile($def_tpl));
        }
        return io_readFile($tpl_path);
    }

}

<?php

use dokuwiki\Form;

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
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
        return [];
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed\>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    public function handle($match, $state) {
        $text = str_replace(["\n", '{{fksnewsfeed>', '}}'], ['', '', ''], $match);
        $param = $this->helper->FKS_helper->extractParamtext($text);
        return [$state, [$param]];
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Doku_Renderer_xhtml $renderer */
            list(, list($param)) = $data;
            $data = $this->helper->loadSimpleNews($param['id']);
            if (empty($data) || ($param['id'] == 0)) {
                $renderer->doc .= '<div class="FKS_newsfeed"><div class="error">' . $this->getLang('news_non_exist') . '</div></div>';
                return true;
            }
            $template = $this->getTemplate();

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
                $r = $this->createNews($data);
                $cache->storeCache($json->encode($r));
                list($c, $div_class_ap) = $r;
            }
            $div_class .= ' ' . $div_class_ap;
            $c = (array)$c;

            foreach ($this->helper->Fields as $k) {
                $template = str_replace('@' . $k . '@', $c[$k], $template);
            }
            $pageID = $param['pageID'];
            $template = str_replace('@share@', $this->createShareFields($param['id'], $c, $pageID), $template);
            $template = str_replace('@edit@', $this->createEditField($param), $template);
            $renderer->doc .= '<div class="' . $div_class . '"  data-id="' . $param['id'] . '">' . $template . '</div>';
        }
        return false;
    }

    private function createShareFields($id, $c, $page_id = "") {
        $html = '';
        $link = $this->helper->getToken((int)$id, $page_id);
        if (auth_quickaclcheck('start') >= AUTH_READ) {
            $html .= '<div ' . 'class="share field">' . "\n";
            $html .= '<div class="Twitt">';
            $html .= '<a href="https://twitter.com/share" data-count="none" data-text="' . $c['name'] . '" class="twitter-share-button" data-url="' . $link . '" data-via="fykosak" data-hashtags="FYKOS">Tweet</a>';
            $html .= '</div>' . "\n";

            $html .= '<div class="FB-msg">';
            $html .= $this->social->facebook->createSend($link);
            $html .= '</div>' . "\n";

            $html .= '<div class="FB-share">';
            $html .= $this->social->facebook->createShare($link);
            $html .= '</div>' . "\n";

            $html .= '<div class="whatsapp-share">';
            $html .= $this->social->whatsapp->createSend($link);
            $html .= '</div>' . "\n";

            $html .= '
<div class="link">
    <span class="link-icon icon"></span>
    <span contenteditable="true" class="link_inp" >' . $link . '</span>
</div>' . "\n";

            $html .= '</div>' . "\n";
        }
        return $html;
    }

    private function createNews($data) {
        $div_class = '';
        $c = [];
        foreach ($this->helper->Fields as $k) {
            switch ($k) {
                case 'image':
                    if ($data['image']) {
                        $div_class .= ' w_image';
                        $c['image'] = '<div class="image"><div class="image_content"><img src="' . ml($data['image'], ['w' => 300]) . '" alt="newsfeed"></div></div>';
                    } else {
                        $c['image'] = '';
                    }
                    break;
                case'text':
                    $info = [];
                    $c['text'] = p_render('xhtml', p_get_instructions($data['text']), $info);
                    break;
                case'category':
                    if (!$data['category']) {
                        $c['category'] = 'default';
                    }
                    $div_class .= ' ' . $c['category'];
                    break;
                case'newsdate':
                    $c['newsdate'] = $this->newsdate($data['newsdate']);
                    break;
                default:
                    $c[$k] = htmlspecialchars($data[$k]);
            }
        }
        return [$c, $div_class];
    }

    /**
     * @param array $param
     * @return string
     */
    private function createEditField($param) {
        if ($param['editable'] === 'true') {
            $html = '<div class="edit" data-id="' . $param["id"] . '">';
            $html .= '<button class="more_option_toggle">' . $this->getLang('btn_opt') . '</button>';
            $html .= '<div class="fields" data-id="' . $param["id"] . '">';
            $html .= $this->getPriorityField($param["id"], $param['stream']);
            $html .= $this->btnEditNews($param["id"], $param['stream']);
            $html .= '</div></div>';
            return $html;
        } else {
            return '';
        }
    }


    private function getPriorityField($id, $stream) {
        $html = '';
        if (auth_quickaclcheck('start') >= AUTH_EDIT) {

            $html .= '<div class="priority field">';

            $form = new Form\Form();
            $form->addClass('block');

            $form->setHiddenField('do', 'show');
            $form->setHiddenField('news_id', $id);
            $form->setHiddenField('news_stream', $stream);
            $form->setHiddenField('news_do', 'priority');
            $form->setHiddenField('target', 'plugin_fksnewsfeed');

            $streamID = $this->helper->streamToID($stream);
            list($priority) = $this->helper->findPriority($id, $streamID);

            $priorityValue = new Form\InputElement('number', 'priority', $this->getLang('valid_from'));
            $priorityValue->val($priority['priority']);
            $form->addElement($priorityValue);

            $priorityFromElement = new Form\InputElement('datetime-local', 'priority_form', $this->getLang('valid_from'));
            $priorityFromElement->val($priority['priority_from']);
            $form->addElement($priorityFromElement);

            $priorityToElement = new Form\InputElement('datetime-local', 'priority_to', $this->getLang('valid_to'));
            $priorityToElement->val($priority['priority_to']);
            $form->addElement($priorityToElement);

            $form->addButton('submit', $this->getLang('btn_save_priority'));
            $html .= $form->toHTML();
            $html .= ' </div > ';
        }

        return $html;
    }


    private function btnEditNews($id, $stream) {
        $html = '';
        if (auth_quickaclcheck('start') >= AUTH_EDIT) {
            $html .= '<div class="opt field" > ';

            $editForm = new Form\Form();
            $editForm->setHiddenField('do', 'edit');
            $editForm->setHiddenField('news_id', $id);
            $editForm->setHiddenField('news_do', 'edit');
            $editForm->setHiddenField('target', 'plugin_fksnewsfeed');
            $editForm->addButton('submit', $this->getLang('btn_edit_news'));
            $html .= $editForm->toHTML();

            $deleteForm = new Form\Form();
            $deleteForm->setHiddenField('news_do', 'delete_save');
            $deleteForm->setHiddenField('target', 'plugin_fksnewsfeed');
            $deleteForm->setHiddenField('stream', $stream);
            $deleteForm->setHiddenField('news_id', $id);
            $deleteForm->addButton('submit', $this->getLang('delete_news'))->attr('data-warning', true);
            $html .= $deleteForm->toHTML();

            $purgeForm = new Form\Form();
            $purgeForm->setHiddenField('fksnewsfeed_purge', 'true');
            $purgeForm->setHiddenField('news_id', $id);
            $purgeForm->addButton('submit', $this->getLang('cache_del'));
            $html .= $purgeForm->toHTML();

            $html .= ' </div > ';
        }
        return $html;
    }

    private function newsdate($date) {

        $date = date('j\. F Y', strtotime($date));
        $enMonth = [
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
        ];
        $langMonth = [
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
        ];
        return (string)str_replace($enMonth, $langMonth, $date);
    }

    private function getTemplate() {
        $tpl_path = wikiFN($this->getConf('tpl'));
        if (!file_exists($tpl_path)) {
            $def_tpl = DOKU_PLUGIN . plugin_directory('fksnewsfeed') . '/tpl.html';
            io_saveFile($tpl_path, io_readFile($def_tpl));
        }
        return io_readFile($tpl_path);
    }

}

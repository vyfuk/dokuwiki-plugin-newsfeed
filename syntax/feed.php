<?php

use dokuwiki\Form;

class syntax_plugin_fksnewsfeed_feed extends DokuWiki_Syntax_Plugin {
    /**
     * @var helper_plugin_fksnewsfeed
     */
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
        return [];
    }

    public function getSort() {
        return 24;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{news-feed>.+?}}', $mode, 'plugin_fksnewsfeed_feed');
    }

    public function handle($match, $state) {
        preg_match_all('/([a-z-_]+)="([^".]*)"/', substr($match, 12, -2), $matches);
        $parameters = [];
        foreach ($matches[1] as $index => $match) {
            $parameters[$match] = $matches[2][$index];
        }
        return [$state, $parameters];
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml') {

            list($state, $param) = $data;
            switch ($state) {
                case DOKU_LEXER_SPECIAL:
                    $renderer->nocache();
                    $news = $this->helper->loadSimpleNews($param['id']);
                    if (is_null($news) || ($param['id'] == 0)) {
                        $renderer->doc .= '<div class="alert alert-danger">' . $this->getLang('news_non_exist') .
                            '</div>';
                        return true;
                    }
                    $content = $this->getContent($news, $param);
                    $renderer->doc .= '<div class="col-12 row mb-3">';
                    $renderer->doc .= '<div class="col-12">';
                    $renderer->doc .= $content;
                    $renderer->doc .= '</div>';
                    $renderer->doc .= '</div>';
                    return false;
                default:
                    return true;
            }
        }
        return false;
    }

    /**
     * @param $data \PluginNewsFeed\News
     * @param $params array
     * @return string
     */
    private function getContent($data, $params) {
        $f = $data->getCacheFile();
        $cache = new cache($f, '');
        $json = new JSON();
        if ($cache->useCache()) {
            $innerHtml = $json->decode($cache->retrieveCache());
        } else {
            $innerHtml = $this->getHeader($data);
            $innerHtml .= $this->getText($data);

            $innerHtml .= $this->getLink($data);
            $innerHtml .= $this->getSignature($data);
            $innerHtml .= $this->getShareFields($params['id'], $data);
            $cache->storeCache($json->encode($innerHtml));
        }

        $html = '<div class="bs-callout mb-3 bs-callout-' . $data->getCategory() . '">';
        $html .= $innerHtml;
        $html .= $this->getEditField($params);
        $html .= '</div>';
        return $html;
    }

    /**
     * @param $id
     * @param $news \PluginNewsFeed\News
     * @return string
     */
    private function getShareFields($id, $news) {
        global $ID;
        if (auth_quickaclcheck('start') < AUTH_READ) {
            return '';
        }
        $link = $this->helper->getToken((int)$id, $news->getLinkHref());
        if (preg_match('|^https?://|', $news->getLinkHref())) {
            $link = $this->helper->getToken((int)$id, $ID);;
        }
        $html = '<div class="row mb-3" style="max-height: 2rem;">';

        $html .= '<div class="col-6">' .
            $this->helper->social->facebook->createSend($link) .
            '</div>';

        $html .= '<div class="col-6">';
        $html .= $this->helper->social->facebook->createShare($link);
        $html .= '</div>';

        //  $html .= '<div class="link">
        //  <span class="link-icon icon"></span>
        //  <span contenteditable="true" class="link_inp" >' . $link . '</span>
        //// </div>' ;

        $html .= '</div>';

        return $html;
    }

    /**
     * @param $news \PluginNewsFeed\News
     * @return null|string
     */
    private function getText($news) {
        return p_render('xhtml', p_get_instructions($news->getText()), $info);
    }

    /**
     * @param $news \PluginNewsFeed\News
     * @return string
     */
    private function getSignature($news) {
        return ' <div class="card-text text-right">
            <a href="mailto:' . hsc($news->getAuthorEmail()) . '" class="mail" title="' . hsc($news->getAuthorEmail()) .
            '"><span class="fa fa-envelope"></span>' . hsc($news->getAuthorName()) . '</a>
        </div>';
    }

    /**
     * @param $news \PluginNewsFeed\News
     * @return string
     */
    private function getHeader($news) {
        return '<h4>' . $news->getTitle() .
            '<small class="float-right">' . $this->newsDate($news->getNewsDate()) . '</small></h4>';
    }

    /**
     * @param $news \PluginNewsFeed\News
     * @return string
     */
    private function getLink($news) {
        if ($news->hasLink()) {
            if (preg_match('|^https?://|', $news->getLinkHref())) {
                $href = hsc($news->getLinkHref());
            } else {
                $href = wl($news->getLinkHref(), null, true);
            }
            return '<p><a class="btn btn-secondary" href="' . $href . '">' . $news->getLinkTitle() . '</a></p>';
        }
        return '';
    }

    private function getEditField($params) {

        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return '';
        }
        $html = '<button data-toggle="modal" data-target="#feedModal' . $params['id'] . '" class="btn btn-primary" >' .
            $this->getLang('btn_opt') . '</button>';
        $html .= '<div id="feedModal' . $params['id'] . '" class="modal" data-id="' . $params["id"] . '">';
        $html .= '<div class="modal-dialog">';
        $html .= '<div class="modal-content">';
        $html .= $this->getModalHeader();
        $html .= $this->getPriorityField($params['id'], $params['stream'], $params);
        $html .= $this->btnEditNews($params['id'], $params['stream']);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        return $html;

    }

    private function getModalHeader() {
        $html = '';
        $html .= '<div class="modal-header">';
        $html .= '<h5 class="modal-title">' . $this->getLang('') . 'Upaviť novinku</h5>';
        $html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close">';
        $html .= '<span aria-hidden="true">×</span>';
        $html .= '</button>';
        $html .= ' </div>';
        return $html;
    }

    private function getPriorityField($id, $stream, $params) {
        $html = '';
        if ($params['editable'] !== 'true') {
            return '';
        }
        if (!$params['stream']) {
            return '';
        }

        $html .= '<div class="modal-body">';
        $form = new Form\Form();
        $form->addClass('block');

        $form->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $id);
        $form->setHiddenField('news[stream]', $stream);
        $form->setHiddenField('news[do]', 'priority');

        $streamID = $this->helper->streamToID($stream);
        $priority = new \PluginNewsFeed\Priority(null, $id, $streamID);
        $priority->fillFromDatabase();
        $form->addTagOpen('div')->addClass('form-group');
        $priorityValue = new Form\InputElement('number', 'priority[value]', $this->getLang('valid_from'));
        $priorityValue->attr('class', 'form-control')->val($priority->getPriorityValue());
        $form->addElement($priorityValue);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('form-group');
        $priorityFromElement = new Form\InputElement('datetime-local', 'priority[from]', $this->getLang('valid_from'));
        $priorityFromElement->val($priority->getPriorityFrom() ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityFromElement);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('form-group');
        $priorityToElement = new Form\InputElement('datetime-local', 'priority[to]', $this->getLang('valid_to'));
        $priorityToElement->val($priority->getPriorityTo() ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityToElement);
        $form->addTagClose('div');

        $form->addButton('submit', $this->getLang('btn_save_priority'))->addClass('btn btn-success');
        $html .= $form->toHTML();
        $html .= ' </div > ';

        return $html;
    }

    private function btnEditNews($id, $stream) {
        $html = '';

        $html .= '<div class="modal-footer"> ';
        $html .= '<div class="btn-group"> ';
        $editForm = new Form\Form();
        $editForm->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $editForm->setHiddenField('news[id]', $id);
        $editForm->setHiddenField('news[do]', 'edit');
        $editForm->addButton('submit', $this->getLang('btn_edit_news'))->addClass('btn btn-info');
        $html .= $editForm->toHTML();

        if ($stream) {
            $deleteForm = new Form\Form();
            $deleteForm->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
            $deleteForm->setHiddenField('news[do]', 'delete');
            $deleteForm->setHiddenField('news[stream]', $stream);
            $deleteForm->setHiddenField('news[id]', $id);
            $deleteForm->addButton('submit', $this->getLang('delete_news'))->attr('data-warning', true)
                ->addClass('btn btn-danger');
            $html .= $deleteForm->toHTML();
        }

        $purgeForm = new Form\Form();
        $purgeForm->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $purgeForm->setHiddenField('news[do]', 'purge');
        $purgeForm->setHiddenField('news[id]', $id);
        $purgeForm->addButton('submit', $this->getLang('cache_del'))->addClass('btn btn-warning');
        $html .= $purgeForm->toHTML();
        $html .= ' </div > ';
        $html .= ' </div > ';

        return $html;
    }

    private function newsDate($date) {

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
}

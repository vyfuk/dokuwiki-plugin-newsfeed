<?php
/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
use dokuwiki\Form\Form;

if (!defined('DOKU_INC')) {
    die;
}

class action_plugin_fksnewsfeed_ajax extends DokuWiki_Action_Plugin {

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    /**
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {


        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'stream');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event event object by reference
     * @return void
     */
    public function stream(Doku_Event &$event) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }

        if ($INPUT->str('news_do') == 'stream' || $INPUT->str('news_do') == 'more') {

            ob_start();
            header('Content-Type: application/json');
            $event->stopPropagation();
            $event->preventDefault();

            $htmlHead = null;
            if ($INPUT->str('news_do') == 'stream') {
                if (auth_quickaclcheck('start') >= $this->getConf('perm_manage')) {
                    $htmlHead .= $this->printCreateBtn($INPUT->str('news_stream'));
                    $htmlHead .= $this->printPullBtn($INPUT->str('news_stream'));
                    $htmlHead .= $this->printCacheBtn();
                }
                if (auth_quickaclcheck('start') >= $this->getConf('perm_rss')) {
                    $htmlHead .= $this->printRSS($INPUT->str('news_stream'));
                }
            }
            $news = $this->helper->loadStream($INPUT->str('news_stream'));
            $data = $this->printStream($news, (int)$INPUT->str('news_feed_s', 0), (int)$INPUT->str('news_feed_l', 3), $INPUT->str('news_stream'), $INPUT->str('page_id'));
            $json = new JSON();

            $data['html']['head'] = $htmlHead;
            echo $json->encode($data);
        } else {
            return;
        }
    }

    public function printStream($news, $start = 0, $length = 5, $stream = "", $page_id = "") {
        $htmlNews = [];
        $htmlButton = null;
        global $INPUT;
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = helper_plugin_fkshelper::_is_even($i);
            $htmlNews[] = $this->helper->printNews($news[$i]['news_id'], $e, $stream, $page_id);
        }
        if ($length + $start >= count($news)) {
            $htmlButton .= '<div class="msg">' . $this->getLang('no_more') . '</div>';
        } else {
            $htmlButton = '<div class="more_news" data-stream="' . $INPUT->str('news_stream') . '" data-view="' . ($length + $start) . '">
            <button class="button">' . $this->getLang('btn_more_news') . '</button>
                </div>';
        }
        return ['html' => ['button' => $htmlButton, 'news' => $htmlNews]];
    }

    private function printPullBtn($stream) {
        $form = new Form();
        $form->setHiddenField('target', 'plugin_fksnewsfeed');
        $form->setHiddenField('do', 'admin');
        $form->setHiddenField('page', 'fksnewsfeed_push');
        $form->setHiddenField('stream', $stream);
        $form->addButton('submit', $this->getLang('btn_push_stream'));
        return $form->toHTML();
    }

    private function printRSS($stream) {
        $html = '';
        $html .= '<div class="rss">';
        $html .= '<a href="' . DOKU_URL . 'feed.php?stream=' . $stream . '"><span class="icon small-btn rss-icon"></span><span class="btn-big">RSS</span></a>';
        $html .= '<span class="link" contenteditable="true" >' . DOKU_URL . 'feed.php?stream=' . $stream . '</span>';
        $html .= '</div>';
        return $html;
    }

    private function printCreateBtn($stream) {
        $form = new Form();
        $form->setHiddenField('do', 'edit');
        $form->setHiddenField('target', 'plugin_fksnewsfeed');
        $form->setHiddenField('news_do', 'create');
        $form->setHiddenField('news_id', 0);
        $form->setHiddenField('news_stream', $stream);
        $form->addButton('submit', $this->getLang('btn_create_news'));
        return $form->toHTML();
    }

    private function printCacheBtn() {
        $form = new Form();
        $form->setHiddenField('fksnewsfeed_purge', 'true');
        $form->addButton('submit', $this->getLang('cache_del_full'));
        return $form->toHTML();
    }

}

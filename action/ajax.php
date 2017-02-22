<?php

/**
 * DokuWiki Plugin fksnewsfeed (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
if (!defined('DOKU_INC')) {
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

    /**
     * @var helper_plugin_fksnewsfeed
     */
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


        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'stream');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function stream(Doku_Event &$event) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }
        require_once DOKU_INC . 'inc/JSON.php';

        if ($INPUT->str('news_do') == 'stream' || $INPUT->str('news_do') == 'more') {

            ob_start();
            header('Content-Type: application/json');
            $event->stopPropagation();
            $event->preventDefault();

            $r = (string)"";
            if ($INPUT->str('news_do') == 'stream') {
                if (auth_quickaclcheck('start') >= $this->getConf('perm_manage')) {
                    $this->printCreateBtn($r, $INPUT->str('news_stream'));
                    $this->printPullBtn($r, $INPUT->str('news_stream'));
                    $this->printCacheBtn($r);
                }
                if (auth_quickaclcheck('start') >= $this->getConf('perm_rss')) {
                    $this->printRSS($r, $INPUT->str('news_stream'));
                }
            }
            $news = $this->helper->loadStream($INPUT->str('news_stream'));
            $more = $this->PrintStream($news, $r, (int)$INPUT->str('news_feed_s', 0), (int)$INPUT->str('news_feed_l', 3), $INPUT->str('news_stream'), $INPUT->str('page_id'));
            $json = new JSON();

            $r = ob_get_contents() . $r;
            ob_end_clean();

            echo $json->encode(['more' => $more, "r" => $r]);
        } else {
            return;
        }
    }

    public function PrintStream($news, &$r, $s = 0, $l = 5, $stream = "", $page_id = "") {
        global $INPUT;
        $more = false;
        for ($i = $s; $i < min([$s + $l, (count($news))]); $i++) {
            $e = $this->helper->_is_even($i);
            $r .= $this->printNews($news[$i]['news_id'], $e, $stream, $page_id);
        }
        if ($l + $s >= count($news)) {
            $more = true;
            $r .= html_open_tag('div', ['class' => 'msg']);
            $r .= $this->getLang('no_more');
            $r .= html_close_tag('div');
        } else {
            $r .= $this->printMoreBtn($INPUT->str('news_stream'), $l + $s);
        }
        return $more;
    }

    public function printNews($id, $e, $stream, $page_id = "") {
        $n = str_replace(['@id@', '@even@', '@edited@', '@stream@', '@page_id@'], [
            $id,
            $e,
            'true',
            $stream,
            $page_id
        ], $this->helper->simple_tpl);
        $info = [];

        return p_render("xhtml", p_get_instructions($n), $info);
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $stream
     * @param int $more
     * @return string
     */
    private function printMoreBtn($stream, $more) {

        return '<div class="more_news" data-stream="' . $stream . '" data-view="' . $more . '">' . '<button class="button">' . $this->getLang('btn_more_news') . '</button>' . '</div>';
    }

    private function printPullBtn(&$r, $stream) {
        $form2 = new Doku_Form(['method' => 'POST', 'class' => 'info']);
        $form2->addHidden('target', 'plugin_fksnewsfeed');
        $form2->addHidden('do', 'admin');
        $form2->addHidden('page', 'fksnewsfeed_push');
        $form2->addHidden('stream', $stream);
        $form2->addElement(form_makeButton('submit', '', $this->getLang('btn_push_stream')));
        ob_start();
        html_form('addnews', $form2);
        $r .= ob_get_contents();
        ob_end_clean();
    }

    private function printRSS(&$r, $stream) {
        $r .= html_open_tag('div', ['class' => 'rss']);

        $r .= '<a href="' . DOKU_URL . 'feed.php?stream=' . $stream . '"><span class="icon small-btn rss-icon"></span><span class="btn-big">RSS</span></a>';
        $r .= '<span class="link" contenteditable="true" >' . DOKU_URL . 'feed.php?stream=' . $stream . '</span>';
        $r .= '</div>';
    }

    private function printCreateBtn(&$r, $stream) {

        $form3 = new Doku_Form(['method' => 'GET', 'class' => 'info']);
        $form3->addHidden('do', 'edit');
        $form3->addHidden('target', 'plugin_fksnewsfeed');
        $form3->addHidden('news_do', 'create');
        $form3->addHidden('news_id', 0);
        $form3->addHidden('news_stream', $stream);
        $form3->addElement(form_makeButton('submit', '', $this->getLang('btn_create_news')));

        ob_start();
        html_form('create_news', $form3);
        $r .= ob_get_contents();
        ob_clean();
    }

    private function printCacheBtn(&$r) {
        ob_start();
        $form3 = new Doku_Form(['class' => 'warning']);
        $form3->addHidden('fksnewsfeed_purge', 'true');

        $form3->addElement(form_makeButton('submit', null, $this->getLang('cache_del_full')));
        html_form('cachenews', $form3);
        $r .= ob_get_contents();
        ob_clean();
    }

}

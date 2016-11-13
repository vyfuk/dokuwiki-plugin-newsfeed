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


        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'Stream');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event event object by reference
     * @param mixed $param [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function Stream(Doku_Event &$event) {
        global $INPUT;
        if ($INPUT->str('target') != 'feed') {
            return;
        }
        require_once DOKU_INC . 'inc/JSON.php';

        if ($INPUT->str('news_do') == 'more') {

            ob_start();
            header('Content-Type: application/json');
            $event->stopPropagation();
            $event->preventDefault();


            $html = [];
            $html['feeds'] = [];


            $news = $this->helper->LoadStream($INPUT->str('news_stream'), true);
            $more = $this->PrintStream($news, $html, (int)$INPUT->str('news_feed_s'), (int)$INPUT->str('news_feed_l'), $INPUT->str('news_stream'), $INPUT->str('page_id'));
            $json = new JSON();

            $html['err'] = ob_get_contents();
            ob_end_clean();

            echo $json->encode(array('more' => $more, "html" => $html));
        } else {
            return;
        }
    }

    public function PrintStream($news, &$html, $s = 0, $l = 5, $stream = "", $page_id = "") {
        global $INPUT;
        $more = false;
        for ($i = $s; $i < min(array($s + $l, (count($news)))); $i++) {
            $e = $this->helper->_is_even($i);
            $html['feeds'][] = $this->PrintNews($news[$i]['news_id'], $e, $stream, $page_id);
        }
        if ($l + $s >= count($news)) {
            $more = true;
            $html['msg'] = '<div class="msg">' . $this->getLang('no_more') . '</div>';
        } else {
            $html['btn'] = $this->PrintMoreBtn($INPUT->str('news_stream'), $l + $s);
        }
        return $more;
    }

    public function PrintNews($id, $e, $stream, $page_id = "") {
        $n = str_replace(array('@id@', '@even@', '@edited@', '@stream@', '@page_id@'), array($id, $e, 'true', $stream, $page_id), $this->helper->simple_tpl);
        $info = array();

        return p_render("xhtml", p_get_instructions($n), $info);
    }

    /**
     * @author Michal Červeňák <miso@fykos.cz>
     * @param string $stream
     * @param int $more
     * @return string
     */
    private function PrintMoreBtn($stream, $more) {

        return '<div class="more_news" data-stream="' . $stream . '" data-view="' . $more . '">' . '<button class="button">' . $this->getLang('btn_more_news') . '</button>' . '</div>';
    }

}

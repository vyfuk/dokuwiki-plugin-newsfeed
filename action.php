<?php

/**
 * DokuWiki Plugin fksdbexport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_fksnewsfeed extends DokuWiki_Action_Plugin {

    private $modFields = array('name', 'email', 'author', 'newsdate', 'text');
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

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('HTML_SECEDIT_BUTTON', 'BEFORE', $this, 'handle_html_secedit_button');
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_html_edit_formselection');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_action_ajax_request');
       
        
    }

    public function handle_html_secedit_button(Doku_Event &$event, $param) {

        if (!p_get_metadata('fks_news')) {
            return;
        }
        //$event->data['name'] = $this->getLang('Edit'); // it's set in redner()
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

        $data["fullhtml"] = $this->helper->renderfullnews($INPUT->str('id'), 'fkseven');

        require_once DOKU_INC . 'inc/JSON.php';
        $json = new JSON();
        header('Content-Type: application/json');
        echo $json->encode($data);
    }

    public function handle_html_edit_formselection(Doku_Event &$event, $param) {
        global $TEXT;
        global $INPUT;
        //print_r();
        if ($INPUT->str('target') !== 'plugin_fksnewsfeed') {

            return;
        }

        $event->preventDefault();
        unset($event->data['intro_locale']);
        echo $this->locale_xhtml('edit_intro');
        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->modFields as $field) {
                $data[$field] = $_POST[$field];
            }
        } else {

            $data = $this->extractParamACT(io_readFile(metaFN($_POST["id"], ".txt")));
        }

        $form->startFieldset('Newsfeed');
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
        if (!isset($_POST['do']['save'])) {
            return;
        }
        global $INPUT;
        global $TEXT;
        global $ID;

        if ($INPUT->str("target") == "plugin_fksnewsfeed") {
            $data = array();
            //print_r($_REQUEST);
            foreach ($this->modFields as $field) {
                if ($field == 'text') {

                    $data[$field] = cleanText($_POST['wikitext']);
                    unset($_POST['wikitext']);
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
            $news.= '<fksnewsfeed
newsdate=' . $data['newsdate'] . ';
author=' . $data['author'] . ';
email= ' . $data['email'] . ';
name=' . $data['name'] . '>
' . $data['text'] . '
</fksnewsfeed>';
            io_saveFile(metaFN($_POST["id"], '.txt'), $news);
            // $TEXT = $news;
            unset($TEXT);
            unset($_POST['wikitext']);
            $ACT="show";
        }
    }


    private function extractParamACT($text) {
        global $TEXT;
        
        $param = $this->helper->extractParamtext_feed($text);
        $TEXT = $param["text"];

        return $param;
    }

}

// vim:ts=4:sw=4:et:


<?php

/**
 * DokuWiki Plugin fksdbexport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class action_plugin_fksnewsfeed extends DokuWiki_Action_Plugin {

    private $modFields = array('name', 'email', 'author', 'newsdate', 'text');

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
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_html_edit_formselection');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    public function handle_html_edit_formselection(Doku_Event &$event, $param) {
        global $TEXT;
        global $INPUT;

        //if ($event->data['target'] !== 'plugin_fksnewsfeed') {
        //    return;
        //}
         if ($_POST['target'] !== 'plugin_fksnewsfeed') {
            return;
        }
        $event->preventDefault();

        unset($event->data['intro_locale']);

        // FIXME: Remove this if you want a media manager fallback link
        // You will probably want a media link if you want a normal toolbar
        //$event->data['media_manager'] = false;

        echo $this->locale_xhtml('edit_intro');


        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->modFields as $field) {
                $parameters[$field] = $_POST[$field];
            }
        } else {
            $parameters = $this->helper->extractParam($TEXT);
        }


        $data = $parameters;
        $globAttr = array();
        $form->startFieldset('Newsfeed');

        // editable fields
        foreach ($this->modFields as $field) {
            $attr = $globAttr;

            if ($field == 'text') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                print_r($value);
                $form->addElement(form_makeWikiText($TEXT, $attr));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, $attr));
            }
        }

        $form->endFieldset();
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {
        if (!isset($_POST['do']['save'])) {
            return;
        }
        global $TEXT;
        global $ID;
        if ($_POST["target"] == "plugin_fksnewsfeed") {
            $data = array();
            print_r($_REQUEST);
            foreach ($this->modFields as $field) {
                if ($field == 'text') {

                    $data[$field] = cleanText($_POST['wikitext']);
                    unset($_POST['wikitext']);
                } else {
                    $data[$field] = $_POST[$field];
                }
            }
            $news.='<newsdate>' . $data['newsdate'] . '</newsdate>';
            $news.='<newsauthor>[[' . $data['email'] . '|' . $data['author'] . ']]</newsauthor>';
            $news.="\n".'==== ' . $data['name'] . ' ==== '."\n";
            $news.=$data['text'];

            $filename = $this->helper->getNewsFile($_POST["id"]);
            $TEXT = $news;
            io_saveFile($filename, $news); //ano som prasa !!
        }
    }

}

// vim:ts=4:sw=4:et:
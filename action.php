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

    private $modFields = array('name', 'author', 'date', 'text');

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
        //$controller->register_hook('TPL_ACT_RENDER', 'AFTER', $this, 'news');
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'handle_html_edit_formselection');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */
    function news(Doku_Event &$event, $param) {
        //print_r($event);
        //print_r($param);
        print_r($INFO['meta']);
        if (isset($meta['plugin_fksnewsfeed'])) {
            $match = $meta['plugin_fksnewsfeed']['nonews'];
        }

        //print_r($this->data);
        $match = 10;
        $to_page.="<div class='fksnewswrapper'>";
        $imax;
        for ($i = 1; true; $i++) {
            $newsurl = getnewsurl($i, 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt');
            if (file_exists($newsurl)) {
                continue;
            } else {
                $imax = $i - 1;
                break;
            }
        }
        //" <span>" . $this->getConf('newsfolder') . "/" . $this->getConf('newsfile') . ".txt</span>";
        $rendernews = preg_split('/;;/', substr(io_readFile("data/meta/newsfeed.csv", FALSE), 1, -1));
        for ($i = 0; $i < count($rendernews); $i++) {

            $rendernewsbool = preg_split('/-/', $rendernews[$i]);
            if ($rendernewsbool[1] == "T") {
                if ($match) {
                    /**
                     * find news wiht max number
                     */
                    $newsurl = getnewsurl($rendernewsbool[0], 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt');
                    $to_page.=rendernews($match, $newsurl);
                } else {
                    break;
                }
            }
        }
        $to_page.="</div>";
        echo $to_page;
    }

    public function handle_html_edit_formselection(Doku_Event &$event, $param) {
        global $TEXT;
        global $INPUT;

        if ($event->data['target'] !== 'plugin_fksnewsfeed') {
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
            $parameters = syntax_plugin_fkstaskrepo_entry::extractParameters($TEXT, $this);
        }

        //$data = $this->helper->getProblemData($parameters['year'], $parameters['series'], $parameters['problem']);
        //$data = array_merge($data, $parameters);

        $globAttr = array();
        if (!$event->data['wr']) {
            $globAttr['readonly'] = 'readonly';
        }

        $form->startFieldset('Newsfeed');
        
        // editable fields
        foreach ($this->modFields as $field) {
            $attr = $globAttr;
            
            if ($field == 'text') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                print_r($value);
                $form->addElement(form_makeWikiText($TEXT, $attr));
            } else if ($field == 'tags') {
                $value = $INPUT->post->str($field, implode(', ', $data[$field]));
                $tags = array_map(function($it) {
                    return $it['tag'];
                }, "" );//$this->helper->getTags()); // TODO default lang

                $attr['data-tags'] = json_encode($tags);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $this->getPluginName() . '-' . $field, null, $attr));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, $attr));
            }
        }

        $form->endFieldset();
    }

}

// vim:ts=4:sw=4:et:
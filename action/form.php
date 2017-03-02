<?php

class action_plugin_fksnewsfeed_form extends DokuWiki_Action_Plugin {

    protected $modFields;
    private $cartesianField = ['email', 'author'];
    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
        $this->modFields = $this->helper->Fields;
    }

    /**
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('HTML_EDIT_FORMSELECTION', 'BEFORE', $this, 'formToNews');
    }

    public function formToNews(Doku_Event &$event) {
        global $TEXT;
        global $ID;
        global $INPUT;
        if ($INPUT->str('target') !== 'plugin_fksnewsfeed') {
            return;
        }
        $event->preventDefault();
        /**
         * @var Doku_Form
         */
        $form = $event->data['form'];

        if (array_key_exists('wikitext', $_POST)) {
            foreach ($this->modFields as $field) {
                $data[$field] = $INPUT->param($field);
            }
        } else {
            if ($INPUT->int('news_id') != null) {
                $data = $this->helper->loadSimpleNews($INPUT->str("news_id"));
                $TEXT = $data['text'];
            } else {
                list($data, $TEXT) = $this->createDefault();
            }
        }

        $form->startFieldset('Newsfeed');
        $form->addHidden('page_id', $ID);
        $form->addHidden('target', 'plugin_fksnewsfeed');
        $form->addHidden('news_id', $INPUT->str("news_id"));
        $form->addHidden('news_do', $INPUT->str('news_do'));

        foreach ($this->modFields as $field) {
            if ($field == 'text') {
                $value = $INPUT->post->str('wikitext', $data[$field]);
                $form->addElement(html_open_tag('div', ['class' => 'clearer']));
                $form->addElement(html_close_tag('div'));
                $form->addElement(form_makeWikiText($TEXT, []));
            } elseif ($field == 'newsdate') {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeField('datetime-local', $field, $value, $this->getLang($field), null, null, ['step' => 1]));
            } elseif ($field == 'category') {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeListboxField($field, [
                    'default',
                    'DSEF',
                    'TSAF',
                    'important',
                    'deprecated'
                ], $value, $this->getLang($field)));
            } elseif ($field == 'image') {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, []));
            } else {
                $value = $INPUT->post->str($field, $data[$field]);
                $form->addElement(form_makeTextField($field, $value, $this->getLang($field), $field, null, [
                    'pattern' => '\S.*',
                    'required' => 'required',
                    'list' => 'news_list_' . $field
                ]));

            }
        }
        foreach ($this->cartesianField as $field) {
            $form->addElement(form_makeDataList('news_list_' . $field, $this->helper->allValues($field)));
        }
        $form->endFieldset();
    }

    private function createDefault() {
        global $INFO;
        return [
            [
                'author' => $INFO['userinfo']['name'],
                'newsdate' => date('Y-m-d\TH:i:s'),
                'email' => $INFO['userinfo']['mail'],
                'text' => $this->getLang('news_text'),
                'name' => $this->getLang('news_name'),
                'image' => '',
                'category' => ''
            ],
            $this->getLang('news_text')
        ];
    }
}

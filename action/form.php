<?php

use \dokuwiki\Form\Form;
use PluginFKSHelper\Form\DateTimeInputElement;

class action_plugin_fksnewsfeed_form extends \DokuWiki_Action_Plugin {

    private static $categories = [
        'fykos-blue',
        'fykos-pink',
        'fykos-line',
        'fykos-purple',
        'fykos-orange',
        'fykos-green',
    ];

    /**
     * @var helper_plugin_fksnewsfeed
     */
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'tplEditNews');
    }

    public function tplEditNews(Doku_Event &$event) {
        global $ACT;
        global $INPUT;
        if ($ACT !== helper_plugin_fksnewsfeed::FORM_TARGET) {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('news')['do']) {
            case'edit':
            case'create':
                $this->getEditForm();
                return;
            case 'preview':
                $this->getStreamPreview($event);
                return;
            default:
                return;
        }
    }

    private function getStreamPreview() {
        global $INPUT;
        if ($INPUT->param('news')['stream']) {
            echo p_render('xhtml', p_get_instructions('{{news-stream>feed="5";stream="' . $INPUT->param('news')['stream'] . '"}}'), $info);
        } else {
            msg('Stream is required.', -1);
        }

    }

    private function getEditForm() {
        global $INPUT;
        global $ID;

        $form = new Form();
        if ($INPUT->param('news')['id'] !== 0) {
            $data = new \PluginNewsFeed\Model\News($this->helper->sqlite, $INPUT->param('news')['id']);
            $data->load();

        } else {
            $data = new \PluginNewsFeed\Model\News($this->helper->sqlite, null);
            $data->loadDefault();
        }
        $form->setHiddenField('page_id', $ID);
        $form->setHiddenField('do', helper_plugin_fksnewsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $INPUT->param('news')['id']);
        $form->setHiddenField('news[do]', 'save');
        $form->setHiddenField('news[stream]', $INPUT->param('news')['stream']);
        $form->addFieldsetOpen('News Feed');

        foreach (helper_plugin_fksnewsfeed::$fields as $field) {
            $input = null;
            $form->addTagOpen('div')->addClass('form-group');

            switch ($field) {
                case'text':
                    $input = $form->addTextarea('text', $this->getLang($field), -1)->attr('class', 'form-control');
                    $input->val($data->getText());
                    break;
                case 'newsDate':
                    $input = new DateTimeInputElement($field, $this->getLang($field));
                    $input->attr('class', 'form-control');
                    $input->setStep(1);
                    $form->addElement($input);
                    $input->val($data->getNewsDate());
                    break;
                case'category':
                    $input = $form->addDropdown('category', static::$categories, $this->getLang($field))->attr('class', 'form-control');
                    $input->val($data->getCategory());
                    break;
                case'image':
                    $input = $form->addTextInput($field, $this->getLang($field))->attr('class', 'form-control');
                    $input->val($data->getImage());
                    break;
                case 'linkHref':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->getLinkHref());
                    break;
                case 'linkTitle':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->getLinkTitle());
                    break;
                case 'authorName':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->getAuthorName());
                    break;
                case 'authorEmail':
                    $input = new \dokuwiki\Form\InputElement('email', $field, $this->getLang($field));
                    $form->addElement($input);
                    $input->val($data->getAuthorEmail());
                    break;
                case 'title':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->getTitle());
                    break;
                default:
                    msg('Not implement input field ' . $field, -1);
            }
            $form->addTagClose('div');
        }
        $form->addFieldsetClose();
        $form->addButton('submit', $this->getLang('save'))->addClass('btn btn-success');
        echo $form->toHTML();
    }
}

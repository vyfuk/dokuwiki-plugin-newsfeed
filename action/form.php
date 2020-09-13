<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Form\Form;
use dokuwiki\Form\InputElement;
use FYKOS\dokuwiki\Extension\PluginFKSHelper\Form\DateTimeInputElement;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;

require_once __DIR__ . '/../../fkshelper/inc/Form/DateTimeInputElement.php';

/**
 * Class action_plugin_newsfeed_form
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed_form extends ActionPlugin {

    private static array $categories = [
        'fykos-blue',
        'fykos-pink',
        'fykos-line',
        'fykos-purple',
        'fykos-orange',
        'fykos-green',
    ];

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function register(EventHandler $controller): void {
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'handleActUnknown');
    }

    public function handleActUnknown(Event $event): void {
        global $ACT;
        global $INPUT;
        if ($ACT !== helper_plugin_newsfeed::FORM_TARGET) {
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
                $this->handleEditForm();
                return;
            case 'preview':
                $this->handleStreamPreview();
                return;
        }
    }

    private function handleStreamPreview(): void {
        global $INPUT;
        if ($INPUT->param('news')['stream']) {
            echo p_render('xhtml', p_get_instructions('{{news-stream>feed="5";stream="' . $INPUT->param('news')['stream'] . '"}}'), $info);
        } else {
            msg('Stream is required.', -1);
        }
    }

    private function handleEditForm(): void {
        global $INPUT;
        global $ID;

        $form = new Form();

        if ($INPUT->param('news')['id'] !== 0) {
            $data = $this->helper->serviceNews->getById($INPUT->param('news')['id']);
        } else {
            $data = new ModelNews($this->helper->sqlite);
            $data->loadDefault();
        }
        $form->setHiddenField('page_id', $ID);
        $form->setHiddenField('do', helper_plugin_newsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $INPUT->param('news')['id']);
        $form->setHiddenField('news[do]', 'save');
        $form->setHiddenField('news[stream]', $INPUT->param('news')['stream']);
        $form->addFieldsetOpen('Novinky na webu');

        foreach (helper_plugin_newsfeed::$fields as $field) {
            $input = null;
            $form->addTagOpen('div')->addClass('form-group');

            switch ($field) {
                case'text':
                    $input = $form->addTextarea('text', $this->getLang($field), -1)->attrs([
                        'class' => 'form-control',
                        'rows' => 10,
                    ]);
                    $input->val($data->text);
                    break;
                case 'newsDate':
                    $input = new DateTimeInputElement($field, $this->getLang($field));
                    $input->attr('class', 'form-control');
                    $input->setStep(1);
                    $form->addElement($input);
                    $input->val($data->newsDate ?: 'now');
                    break;
                case'category':
                    $input = $form->addDropdown('category', static::$categories, $this->getLang($field))->attr('class', 'form-control');
                    $input->val($data->category);
                    break;
                case'image':
                    $input = $form->addTextInput($field, $this->getLang($field))->attr('class', 'form-control');
                    $input->val($data->image);
                    break;
                case 'linkHref':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->linkHref);
                    break;
                case 'linkTitle':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->linkTitle);
                    break;
                case 'authorName':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->authorName);
                    break;
                case 'authorEmail':
                    $input = new InputElement('email', $field, $this->getLang($field));
                    $input->attr('class', 'form-control');
                    $form->addElement($input);
                    $input->val($data->authorEmail);
                    break;
                case 'title':
                    $input = $form->addTextInput($field, $this->getLang($field))->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->title);
                    break;
                default:
                    msg('Not implement input field ' . $field, -1);
            }
            if ($note = $this->getLang($field . '_note')) {
                $form->addHTML('<small>' . $note . '</small>');
            }
            $form->addTagClose('div');
        }
        $form->addFieldsetClose();
        $form->addButton('submit', $this->getLang('save'))->addClass('btn btn-success');
        echo $form->toHTML();
    }
}

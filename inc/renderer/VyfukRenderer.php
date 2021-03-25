<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Renderer;

use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use dokuwiki\Form\Form;
use dokuwiki\Form\InputElement;

/**
 * Class VyfukRenderer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class VyfukRenderer extends AbstractRenderer {

    public function render(string $innerHtml, string $formHtml, ModelNews $news): string {
        $html = '<div class="col-12">';
        $html .= '<div class="card mb-2">';
        $html .= $innerHtml;
        $html .= '</div>';
        $html .= $formHtml;
        $html .= '</div>';
        return $html;
    }

    protected function getHeader(ModelNews $news) {
        return '<h3 class="card-header"><i class="fa fa-newspaper-o" aria-hidden="true"></i> ' . $news->title .
            '<small class="pull-right"><i class="fa fa-calendar" aria-hidden="true"></i> ' .
            $news->getLocalDate(function ($key) {
                return $this->helper->getLang($key);
            }) . '</small></h3>';
    }

    public function renderContent(ModelNews $data, array $params): string {
        $innerHtml = $this->getHeader($data);

        $innerHtml .= '<div class="card-body">';
        $innerHtml .= $this->getText($data);
        $innerHtml .= $this->getSignature($data);
        $innerHtml .= $this->getLink($data);
        $innerHtml .= '</div>';
        return $innerHtml;
    }

    public function renderEditFields(array $params): string {

        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return '';
        }
        $html = sprintf('<button data-toggle="collapse" data-target="#feedCollapse%d"
            class="btn btn-primary mb-4 pull-right" ><i class="fa fa-sort-numeric-asc" aria-hidden="true"></i> %s</button>',
            $params['id'], 'Změnit prioritu novinky');
        $html .= $this->btnEditNews($params['id'], $params['stream']);
        $html .= '<div class="clearfix mb-2"></div>';
        $html .= sprintf('<div id="feedCollapse%1$d" class="collapse">', $params['id']);
        $html .= $this->getPriorityField($params['id'], $params['stream'], $params);
        $html .= '</div>';
        return $html;
    }

    /**
     * @param $id
     * @param $streamName
     * @param $params
     * @return string
     */
    protected function getPriorityField($id, $streamName, $params) {
        $html = '<p>Novinky na stránce jsou řazeny podle data. Přiřazením priority je možné novinku posunout výše či níže na stránce.<br>';
        $html .= '<i><b>Upozornění:</b> Ve výchozím nastavení mají všechny novinky prioritu 0!</i></p>';
        if ($params['editable'] !== 'true') {
            return '';
        }
        if (!$params['stream']) {
            return '';
        }

        $form = new Form();
        $form->addClass('row no-gutters');

        $form->setHiddenField('do', \helper_plugin_newsfeed::FORM_TARGET);
        $form->setHiddenField('news[id]', $id);
        $form->setHiddenField('news[stream]', $streamName);
        $form->setHiddenField('news[do]', 'priority');

        $stream = $this->helper->serviceStream->findByName($streamName);

        $priority = $this->helper->servicePriority->findByNewsAndStream($id, $stream->streamId);
        $form->addTagOpen('div')->addClass('col-4 form-group');
        $priorityValue = new InputElement('number', 'priority[value]', $this->helper->getLang('priority_value'));
        $priorityValue->attr('class', 'form-control')->val($priority->getPriorityValue());
        $form->addElement($priorityValue);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('col-4 form-group');
        $priorityFromElement = new InputElement('datetime-local', 'priority[from]', $this->helper->getLang('valid_from'));
        $priorityFromElement->val($priority->priorityFrom ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityFromElement);
        $form->addTagClose('div');

        $form->addTagOpen('div')->addClass('col-4 form-group');
        $priorityToElement = new InputElement('datetime-local', 'priority[to]', $this->helper->getLang('valid_to'));
        $priorityToElement->val($priority->priorityTo ?: date('Y-m-d\TH:i:s', time()))
            ->attr('class', 'form-control');
        $form->addElement($priorityToElement);
        $form->addTagClose('div');

        $form->addButtonHTML('submit', '<i class="fa fa-floppy-o" aria-hidden="true"></i> ' .
            $this->helper->getLang('btn_save_priority'))->addClass('btn btn-success m-auto');
        $html .= $form->toHTML();

        return $html;
    }

    protected function btnEditNews($id, $stream) {
        $html = '';

        $html .= '<div class="d-flex pull-left">';
        $editForm = new Form();
        $editForm->setHiddenField('do', \helper_plugin_newsfeed::FORM_TARGET);
        $editForm->setHiddenField('news[id]', $id);
        $editForm->setHiddenField('news[do]', 'edit');
        $editForm->addButtonHTML('submit', '<i class="fa fa-pencil" aria-hidden="true"></i> ' .
            $this->helper->getLang('btn_edit_news'))->addClass('btn btn-info m-1');
        $html .= $editForm->toHTML();

        if ($stream) {
            $deleteForm = new Form();
            $deleteForm->setHiddenField('do', \helper_plugin_newsfeed::FORM_TARGET);
            $deleteForm->setHiddenField('news[do]', 'delete');
            $deleteForm->setHiddenField('news[stream]', $stream);
            $deleteForm->setHiddenField('news[id]', $id);
            $deleteForm->addButtonHTML('submit', '<i class="fa fa-trash-o" aria-hidden="true"></i> ' .
                $this->helper->getLang('delete_news'))
                ->attr('data-warning', true)->addClass('btn btn-danger m-1');
            $html .= $deleteForm->toHTML();
        }

        $purgeForm = new Form();
        $purgeForm->setHiddenField('do', \helper_plugin_newsfeed::FORM_TARGET);
        $purgeForm->setHiddenField('news[do]', 'purge');
        $purgeForm->setHiddenField('news[id]', $id);
        $purgeForm->addButtonHTML('submit', '<i class="fa fa-trash-o" aria-hidden="true"></i> ' .
            $this->helper->getLang('cache_del'))->addClass('btn btn-warning m-1');
        $html .= $purgeForm->toHTML();
        $html .= '</div>';

        return $html;
    }

    /**
     * @param $news ModelNews
     * @return null|string
     */
    protected function getText(ModelNews $news) {
        return $news->renderText();
    }

    /**
     * @param $news ModelNews
     * @return string
     */
    protected function getSignature(ModelNews $news) {
        return '<div class="card-text text-right">
            <a href="mailto:' . hsc($news->authorEmail) . '" class="mail" title="' . hsc($news->authorEmail) .
            '"><span class="fa fa-envelope"></span> ' . hsc($news->authorName) . '</a>
        </div>';
    }

    /**
     * @param $news ModelNews
     * @return string
     */
    protected function getLink(ModelNews $news) {
        if ($news->hasLink()) {
            if (preg_match('|^https?://|', $news->linkHref)) {
                $href = hsc($news->linkHref);
            } else {
                $href = wl($news->linkHref, null, true);
            }
            return '<div class="text-center"><a class="btn btn-outline-primary mt-1 mb-1 pl-4 pr-4" href="'
                . $href . '">' . $news->linkTitle . '</a></div>';
        }
        return '';
    }
}

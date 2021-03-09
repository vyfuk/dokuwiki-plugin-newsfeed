<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Form\InputElement;
use FYKOS\dokuwiki\Extension\PluginFKSHelper\Form\DateTimeInputElement;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use dokuwiki\Form\Form;
use dokuwiki\Cache\Cache;

require_once __DIR__ . '/../fkshelper/inc/Form/DateTimeInputElement.php';

/**
 * Class action_plugin_newsfeed
 * @author Michal Červeňák <miso@fykos.cz>
 */
class action_plugin_newsfeed extends ActionPlugin {

    private static $categories = [
        'fykos-blue',
        'fykos-pink',
        'fykos-line',
        'fykos-purple',
        'fykos-orange',
        'fykos-green',
    ];

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function register(EventHandler $controller): void {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjax');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'handleMetaData');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleActPreprocess');
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

    public function handleActPreprocess(Event $event): void {
        global $INPUT;
        if ($event->data !== helper_plugin_newsfeed::FORM_TARGET) {
            return;
        }
        if (auth_quickaclcheck('start') < AUTH_EDIT) {
            return;
        }
        $event->preventDefault();
        $event->stopPropagation();
        switch ($INPUT->param('news')['do']) {
            case 'create':
            case 'edit':
            default:
                return;
            case'save':
                $this->handleNews();
                return;
            case'priority':
                $this->handlePriority();
                return;
            case'delete':
                $this->handleDelete();
                return;
            case'purge':
                $this->handleCache();
                return;
        }
    }

    public function handleAjax(Event $event): void {
        $payload = json_decode(file_get_contents('php://input'), true);
        if ($payload['target'] !== 'feed') {
            return;
        }
        header('Content-Type: application/json');
        $event->stopPropagation();
        $event->preventDefault();

        echo json_encode($this->printStream((int)$payload['news']['offset'], (int)$payload['news']['length'], $payload['news']['stream'], $payload['page_id']));
    }

    public function handleMetaData(): void {
        global $ID;
        global $INPUT;
        if (!$INPUT->str('news-id')) {
            return;
        }

        $newsId = $INPUT->str('news-id');
        $news = $this->helper->serviceNews->getById($newsId);

        $this->helper->openGraphData->addMetaData('og', 'title', $news->title);
        $this->helper->openGraphData->addMetaData('og', 'url', $news->getToken($ID));
        $text = $news->renderText();
        $this->helper->openGraphData->addMetaData('og', 'description', $text);
        if ($news->hasImage()) {
            $this->helper->openGraphData->addMetaData('og', 'image', ml($news->image, null, true, '&', true));
        }
    }

    private function printStream(int $start, int $length, string $streamId, string $pageId): array {

        $stream = $this->helper->serviceStream->findByName($streamId);

        $news = $stream->getNews();
        $htmlNews = [];
        for ($i = $start; $i < min([$start + $length, (count($news))]); $i++) {
            $e = $i % 2 ? 'even' : 'odd';
            $htmlNews[] = $news[$i]->render($e, $streamId, $pageId);
        }
        return ['html' => ['news' => $htmlNews]];
    }


    private function handleNews(): void {
        global $INPUT;
        $file = ModelNews::getCacheFileById($INPUT->param('news')['id']);
        $cache = new Cache($file, '');
        $cache->removeCache();

        $data = [];
        foreach (helper_plugin_newsfeed::$fields as $field) {
            if ($field === 'text') {
                $data[$field] = cleanText($INPUT->str('text'));
            } else {
                $data[$field] = $INPUT->param($field);
            }
        }
        $data = [
            'title' => $data['title'],
            'author_name' => $data['authorName'],
            'author_email' => $data['authorEmail'],
            'text' => $data['text'],
            'news_date' => $data['newsDate'],
            'image' => $data['image'] ?: null,
            'category' => $data['category'],
            'link_href' => $data['linkHref'] ?: null,
            'link_title' => $data['linkTitle'] ?: null,
        ];

        if (+$INPUT->param('news')['id'] === 0) {
            $this->helper->serviceNews->create($data);
            $this->saveIntoStreams($this->helper->serviceNews->getMaxId(), $INPUT->param('news')['stream']);
        } else {
            $news = $this->helper->serviceNews->getById($INPUT->param('news')['id']);
            $this->helper->serviceNews->update($news, $data);
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function saveIntoStreams(int $newsId, string $streamName): void {
        $stream = $this->helper->serviceStream->findByName($streamName);

        $streams = [$stream->streamId];
        $this->helper->fullParentDependence($stream->streamId, $streams);

        foreach ($streams as $stream) {
            $this->helper->servicePriority->store($newsId, $stream);
        }
    }

    private function handlePriority(): void {
        global $INPUT;
        $file = ModelNews::getCacheFileById($INPUT->param('news')['id']);

        $cache = new cache($file, '');
        $cache->removeCache();
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);

        $priority = $this->helper->servicePriority->findByNewsAndStream($INPUT->param('news')['id'], $stream->streamId);
        $data = $INPUT->param('priority');
        $this->helper->servicePriority->update($priority, [
            'priority_from' => $data['from'],
            'priority_to' => $data['to'],
            'priority' => $data['value'],
        ]);
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function handleDelete(): void {
        global $INPUT;
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);
        $priority = $this->helper->servicePriority->findByNewsAndStream($INPUT->param('news')['id'], $stream->streamId);
        $priority->delete();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }

    private function handleCache(): void {
        global $INPUT;
        if (!$INPUT->param('news')['id']) {
            $news = $this->helper->serviceNews->getAll();
            foreach ($news as $new) {
                $f = $new->getCacheFile();
                $cache = new Cache($f, '');
                $cache->removeCache();
            }
        } else {
            $f = ModelNews::getCacheFileById($INPUT->param('news')['id']);
            $cache = new Cache($f, '');
            $cache->removeCache();
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
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
        $form->addHTML('<h1>' . $this->getLang('btn_create_news') . '</h1>');
        $form->addTagOpen('div')->addClass('row');

        foreach (helper_plugin_newsfeed::$fields as $field) {
            $input = null;

            switch ($field) {
                case 'title':
                    $input = $form->addTextInput($field, $this->getLang($field))->addClass('col-12')->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->title);
                    break;
                case 'authorName':
                    $input = $form->addTextInput($field, $this->getLang($field))->addClass('col-6')->attrs([
                        'pattern' => '\S.*',
                        'required' => 'required',
                        'class' => 'form-control',
                    ]);
                    $input->val($data->authorName);
                    break;
                case 'authorEmail':
                    $input = new InputElement('email', $field, $this->getLang($field));
                    $input->addClass('col-6')->attr('class', 'form-control');
                    $form->addElement($input);
                    $input->val($data->authorEmail);
                    break;
                case 'newsDate':
                    $input = new DateTimeInputElement($field, $this->getLang($field));
                    $input->addClass('col-6')->attr('class', 'form-control');
                    $input->setStep(1);
                    $form->addElement($input);
                    $input->val($data->newsDate ?: 'now');
                    break;
                case'image':
                    $input = $form->addTextInput($field, $this->getLang($field))->addClass('col-6')
                        ->attr('class', 'form-control');
                    $input->val($data->image);
                    break;
                case'category':
                    $input = $form->addDropdown('category', static::$categories, $this->getLang($field))
                        ->addClass('col-12')->attr('class', 'form-control')->attr('disabled', 'true');
                    $input->val($data->category);
                    break;
                case'text':
                    $input = $form->addTextarea('text', $this->getLang($field), -1)->addClass('col-12')
                        ->attr('rows', 5);
                    $input->val($data->text);
                    break;
                case 'linkHref':
                    $input = $form->addTextInput($field, $this->getLang($field))->addClass('col-6')->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->linkHref);
                    break;
                case 'linkTitle':
                    $input = $form->addTextInput($field, $this->getLang($field))->addClass('col-6')->attrs([
                        'class' => 'form-control',
                    ]);
                    $input->val($data->linkTitle);
                    break;
                default:
                    msg('Not implement input field ' . $field, -1);
            }
        }
        $form->addTagClose('div');
        $form->addFieldsetClose();
        $form->addButton('submit', $this->getLang('publish'))->addClass('d-block btn btn-success m-auto w-50');
        echo $form->toHTML();
    }
}

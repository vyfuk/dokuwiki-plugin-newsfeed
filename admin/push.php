<?php

use dokuwiki\Extension\AdminPlugin;
use dokuwiki\Form\Form;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelStream;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;

/**
 * Class admin_plugin_newsfeed_push
 * @author Michal Červeňák <miso@fykos.cz>
 */
class admin_plugin_newsfeed_push extends AdminPlugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function getMenuSort(): int {
        return 291;
    }

    public function forAdminOnly(): bool {
        return false;
    }

    public function getMenuText($lang): string {
        return $this->getLang('push_menu');
    }

    public function handle() {
        global $INPUT;

        if (!checkSecurityToken()) {
            return;
        }
        $stream = $this->helper->serviceStream->findByName($INPUT->param('news')['stream']);
        $newsId = $INPUT->param('news')['id'];

        if ($stream && $newsId) {
            $targetStreamId = $stream->streamId;
            $streamIds = [$targetStreamId];
            if ($INPUT->str('all_dependence')) {
                $this->helper->fullParentDependence($targetStreamId, $streamIds);
            }

            foreach ($streamIds as $streamId) {
                $this->helper->servicePriority->store($newsId, $streamId);
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html(): void {
        global $INPUT;
        $streamName = $INPUT->param('news')['stream'];
        $stream = $this->helper->serviceStream->findByName($streamName);
        echo '<h1>' . $this->getLang('push_menu') . '</h1>';
        echo '<div class="info">' . $this->getLang('push_in_stream') . ': ' . $stream->name . '</div>';

        $streams = $this->helper->serviceStream->getAll();
        echo $this->getChangeStreamForm($streams)->toHTML();

        if ($stream->name) {
            echo '<h2>' . $this->getLang('push_menu') . ': ' . $stream->name . '</h2>';

            $newsInStream = $this->newsToId($stream->getNews());
            $allNews = $this->helper->serviceNews->getAll();
            foreach ($this->newsToId($allNews) as $id) {
                if (array_search($id, $newsInStream) === false) {
                    $news = $this->helper->serviceNews->getById($id);
                    echo $news->render('even', ' ', ' ', false);
                    echo $this->newsAddForm($stream->name, $id);
                    echo '<hr class="clearfix"/>';
                    tpl_flush();
                }
            }
        }
    }

    /**
     * @param $news ModelNews[]
     * @return integer[]
     */
    private function newsToId(array $news): array {
        return array_map(function (ModelNews $value) {
            return $value->newsId;
        }, $news);
    }

    /**
     * @param $streamValues ModelStream[]
     * @return Form
     *
     */
    private function getChangeStreamForm(array $streamValues = []): Form {
        $form = new Form();
        $form->addDropdown('news[stream]', array_map(function (ModelStream $value) {
            return $value->name;
        }, $streamValues), $this->getLang('stream'));
        $form->addButton('submit', $this->getLang('push_choose_stream'));
        return $form;
    }

    private function newsAddForm(string $stream, int $newsId): string {
        $newsForm = new Form();
        $newsForm->setHiddenField('do', 'admin');
        $newsForm->setHiddenField('page', 'newsfeed_push');
        $newsForm->setHiddenField('news[do]', 'push');
        $newsForm->setHiddenField('news[id]', $newsId);
        $newsForm->setHiddenField('news[stream]', $stream);
        $newsForm->addCheckbox('all_dependence', $this->getLang('alw_dep'));
        $newsForm->addButton('submit', $this->getLang('btn_push_news') . $stream);
        return $newsForm->toHTML();
    }
}

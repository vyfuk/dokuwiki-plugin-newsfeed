<?php

use dokuwiki\Extension\AdminPlugin;
use \dokuwiki\Form\Form;
use \PluginNewsFeed\Model\Stream;
use \PluginNewsFeed\Model\Priority;
use \PluginNewsFeed\Model\News;

/**
 * Class admin_plugin_fksnewsfeed_push
 * @author Michal Červeňák <miso@fykos.cz>
 */
class admin_plugin_fksnewsfeed_push extends AdminPlugin {

    private helper_plugin_fksnewsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
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
        $stream = new Stream($this->helper->sqlite);
        $stream->findByName($INPUT->param('news')['stream']);
        $newsId = $INPUT->param('news')['id'];

        if ($stream && $newsId) {
            $targetStreamId = $stream->getStreamId();
            $streamIds = [$targetStreamId];
            if ($INPUT->str('all_dependence')) {
                $this->helper->fullParentDependence($targetStreamId, $streamIds);
            }

            foreach ($streamIds as $streamId) {
                $priority = new Priority($this->helper->sqlite, null, $newsId, $streamId);
                $priority->create();
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
    }

    public function html(): void {
        global $INPUT;
        $streamName = $INPUT->param('news')['stream'];
        $stream = new Stream($this->helper->sqlite);
        $stream->findByName($streamName);
        echo '<h1>' . $this->getLang('push_menu') . '</h1>';
        echo '<div class="info">' . $this->getLang('push_in_stream') . ': ' . $stream->getName() . '</div>';

        $streams = $this->helper->getAllStreams();
        echo $this->getChangeStreamForm($streams)->toHTML();

        if ($stream->getName()) {
            echo '<h2>' . $this->getLang('push_menu') . ': ' . $stream->getName() . '</h2>';

            $newsInStream = $this->newsToId($stream->getNews());
            $allNews = $this->helper->getAllNewsFeed();

            foreach ($this->newsToId($allNews) as $id) {
                if (array_search($id, $newsInStream) === false) {
                    $news = new News($this->helper->sqlite, $id);
                    echo $news->render('even', ' ', ' ', false);
                    echo $this->newsAddForm($stream->getName(), $id);
                    echo '<hr class="clearfix"/>';
                    tpl_flush();
                }
            }
        }
    }

    /**
     * @param $news News[]
     * @return integer[]
     */
    private function newsToId(array $news): array {
        return array_map(function (News $value) {
            return $value->getNewsId();
        }, $news);
    }

    /**
     * @param $streamValues Stream[]
     * @return Form
     *
     */
    private function getChangeStreamForm(array $streamValues = []): Form {
        $form = new Form();
        $form->addDropdown('news[stream]', array_map(function (Stream $value) {
            return $value->getName();
        }, $streamValues), $this->getLang('stream'));
        $form->addButton('submit', $this->getLang('push_choose_stream'));
        return $form;
    }

    private function newsAddForm(string $stream, int $newsId): string {
        $newsForm = new Form();
        $newsForm->setHiddenField('do', 'admin');
        $newsForm->setHiddenField('page', 'fksnewsfeed_push');
        $newsForm->setHiddenField('news[do]', 'push');
        $newsForm->setHiddenField('news[id]', $newsId);
        $newsForm->setHiddenField('news[stream]', $stream);
        $newsForm->addCheckbox('all_dependence', $this->getLang('alw_dep'));
        $newsForm->addButton('submit', $this->getLang('btn_push_news') . $stream);
        return $newsForm->toHTML();
    }
}

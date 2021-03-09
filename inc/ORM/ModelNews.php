<?php

namespace FYKOS\dokuwiki\Extension\PluginNewsFeed\Model;

use helper_plugin_sqlite;

class ModelNews extends AbstractModel {

    const SIMPLE_RENDER_PATTERN = '{{news-feed>id="@id@" even="@even@" editable="@editable@" stream="@stream@" page_id="@page_id@"}}';

    public $newsId;

    public $title;

    public $authorName;

    public $authorEmail;

    public $text;

    public $newsDate;

    public $image;

    public $category;

    public $linkHref;

    public $linkTitle;

    private $priority;

    public function getPriority(): ModelPriority {
        return $this->priority;
    }

    public function renderText(string $mode = 'xhtml'): ?string {
        return p_render($mode, p_get_instructions($this->text), $info);
    }

    public function getLocalDate(callable $getLang): string {
        $date = date('j\. F Y', strtotime($this->newsDate));
        $enMonth = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        $langMonth = [
            $getLang('jan'),
            $getLang('feb'),
            $getLang('mar'),
            $getLang('apr'),
            $getLang('may'),
            $getLang('jun'),
            $getLang('jul'),
            $getLang('aug'),
            $getLang('sep'),
            $getLang('oct'),
            $getLang('now'),
            $getLang('dec'),
        ];
        return (string)str_replace($enMonth, $langMonth, $date);
    }

    public function hasImage(): bool {
        return (bool)$this->image;
    }

    public function hasLink(): bool {
        return (bool)$this->linkHref;
    }

    public function getToken($pageId = ''): string {
        return (string)wl($pageId, null, true) . '?news-id=' . $this->newsId;
    }

    public function getCacheFile(): string {
        return static::getCacheFileById($this->newsId);
    }

    public static function getCacheFileById(int $id): string {
        return 'news-feed_news_' . $id;
    }

    public function render(string $even, string $stream, string $pageId = '', bool $editable = true): ?string {
        $renderPattern = str_replace(['@id@', '@even@', '@editable@', '@stream@', '@page_id@'],
            [
                $this->newsId,
                $even,
                $editable ? 'true' : 'false',
                $stream,
                $pageId,
            ],
            self::SIMPLE_RENDER_PATTERN);
        $info = [];
        return p_render('xhtml', p_get_instructions($renderPattern), $info);
    }

    public function setPriority(ModelPriority $priority): void {
        $this->priority = $priority;
    }

    public static function createFromArray(helper_plugin_sqlite $helperPluginSqlite, array $data): self {
        $model = new self($helperPluginSqlite);
        $model->newsId = $data['news_id'];
        $model->title = $data['title'];
        $model->authorName = $data['author_name'];
        $model->authorEmail = $data['author_email'];
        $model->text = $data['text'];
        $model->newsDate = $data['news_date'];
        $model->image = $data['image'];
        $model->category = $data['category'];
        $model->linkHref = $data['link_href'];
        $model->linkTitle = $data['link_title'];
        return $model;
    }

    public function loadDefault(): self {
        global $INFO;
        $this->authorName = $INFO['userinfo']['name'];
        $this->newsDate = date('Y-m-d\TH:i:s');
        $this->authorEmail = $INFO['userinfo']['mail'];
        return $this;
    }
}

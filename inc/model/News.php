<?php

namespace FYKOS\dokuwiki\Extenstion\PluginNewsFeed\Model;

use helper_plugin_sqlite;

class News extends AbstractModel {

    const SIMPLE_RENDER_PATTERN = '{{news-feed>id="@id@" even="@even@" editable="@editable@" stream="@stream@" page_id="@page_id@"}}';

    /**
     * @var integer
     */
    private $newsId;

    /**
     * @param int $newsId
     */
    public function setNewsId($newsId) {
        $this->newsId = $newsId;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @param string $authorName
     */
    public function setAuthorName($authorName) {
        $this->authorName = $authorName;
    }

    /**
     * @param string $authorEmail
     */
    public function setAuthorEmail($authorEmail) {
        $this->authorEmail = $authorEmail;
    }

    /**
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

    /**
     * @param string $newsDate
     */
    public function setNewsDate($newsDate) {
        $this->newsDate = $newsDate;
    }

    /**
     * @param string $image
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * @param string $category
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * @param string $linkHref
     */
    public function setLinkHref($linkHref) {
        $this->linkHref = $linkHref;
    }

    /**
     * @param string $linkTitle
     */
    public function setLinkTitle($linkTitle) {
        $this->linkTitle = $linkTitle;
    }

    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $authorName;
    /**
     * @var string
     */
    private $authorEmail;
    /**
     * @var string
     */
    private $text;
    /**
     * @var string
     */
    private $newsDate;
    /**
     * @var string
     */
    private $image;
    /**
     * @var string
     */
    private $category;
    /**
     * @var string
     */
    private $linkHref;
    /**
     * @var string
     */
    private $linkTitle;

    /**
     * @var Priority
     */
    private $priority;

    /**
     * @return Priority
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getNewsId() {
        return $this->newsId;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAuthorName() {
        return $this->authorName;
    }

    /**
     * @return string
     */
    public function getAuthorEmail() {
        return $this->authorEmail;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    public function renderText($mode = 'xhtml'): ?string {
        return p_render($mode, p_get_instructions($this->getText()), $info);
    }

    /**
     * @return string
     */
    public function getNewsDate() {
        return $this->newsDate;
    }

    public function getLocalDate(\Closure $getLang) {
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

    /**
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getLinkHref() {
        return $this->linkHref;
    }

    /**
     * @return string
     */
    public function getLinkTitle() {
        return $this->linkTitle;
    }

    /**
     * @return bool
     */
    public function hasImage() {
        return $this->image != null;
    }

    /**
     * @return bool
     */
    public function hasLink() {
        return $this->linkHref != null;
    }

    public function create() {
        $this->sqlite->query('INSERT INTO news  (title,  author_name,  author_email , text,  news_date,
image,  category, link_href,  link_title)  VALUES(?,?,?,?,?,?,?,?,?) ',
            $this->title,
            $this->authorName,
            $this->authorEmail,
            $this->text,
            $this->newsDate,
            $this->image,
            $this->category,
            $this->linkHref,
            $this->linkTitle);
        return $this->findMaxNewsId();
    }

    public function update() {
        $this->sqlite->query('UPDATE news SET title=?,  author_name=?,  author_email=? , text=?, news_date=?,
image=?,  category=?, link_href=?,  link_title=? WHERE news_id=? ',
            $this->title,
            $this->authorName,
            $this->authorEmail,
            $this->text,
            $this->newsDate,
            $this->image,
            $this->category,
            $this->linkHref,
            $this->linkTitle,
            $this->newsId);
    }

    public function getToken($pageId = '') {
        return (string)wl($pageId, null, true) . '?news-id=' . $this->newsId;
    }

    public function getCacheFile() {
        return static::getCacheFileById($this->newsId);
    }

    public static function getCacheFileById($id): string {
        return 'news-feed_news_' . $id;
    }

    public function render($even, $stream, $pageId = '', $editable = true) {
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

    public function setPriority(Priority $priority) {
        $this->priority = $priority;
    }

    public function load(): void {
        $res = $this->sqlite->query('SELECT * FROM news WHERE news_id=?', $this->newsId);
        $row = (object)$this->sqlite->res2row($res);
        //$this->newsId = $row->news_id;
        $this->title = $row->title;
        $this->authorName = $row->author_name;
        $this->authorEmail = $row->author_email;
        $this->text = $row->text;
        $this->newsDate = $row->news_date;
        $this->image = $row->image;
        $this->category = $row->category;
        $this->linkHref = $row->link_href;
        $this->linkTitle = $row->link_title;
    }

    public function loadDefault(): self {
        global $INFO;
        $this->authorName = $INFO['userinfo']['name'];
        $this->newsDate = date('Y-m-d\TH:i:s');
        $this->authorEmail = $INFO['userinfo']['mail'];
        return $this;
    }

    public function __construct(helper_plugin_sqlite &$sqlite, $newsId = null) {
        parent::__construct($sqlite);
        $this->newsId = $newsId;
    }
}

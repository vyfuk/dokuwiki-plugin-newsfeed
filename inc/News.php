<?php

namespace PluginNewsFeed;

class News {
    /**
     * @var integer
     */
    private $newsID;
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
    public function getNewsID() {
        return $this->newsID;
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

    /**
     * @return string
     */
    public function getNewsDate() {
        return $this->newsDate;
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

    public function getCacheFile() {
        return self::getCacheFileByID($this->newsID);
    }

    public static function getCacheFileByID($id) {
        return 'news-feed_news_' . $id;
    }

    public function __construct($data, Priority $priority = null) {
        $this->newsID = $data['news_id'];
        $this->title = $data['title'];
        $this->authorName = $data['author_name'];
        $this->authorEmail = $data['author_email'];
        $this->text = $data['text'];
        $this->newsDate = $data['news_date'];
        $this->image = $data['image'];
        $this->category = $data['category'];
        $this->linkHref = $data['link_href'];
        $this->linkTitle = $data['link_title'];
        $this->priority = $priority;
    }
}

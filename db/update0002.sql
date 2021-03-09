CREATE TABLE `news`
(
    `news_id`      INTEGER PRIMARY KEY AUTO_INCREMENT,
    `title`        VARCHAR(64) NOT NULL,
    `author_name`  VARCHAR(64) NOT NULL,
    `author_email` VARCHAR(64) NOT NULL,
    `text`         TEXT        NOT NULL,
    `news_date`    VARCHAR(32) NOT NULL,
    `image`        VARCHAR(64) NULL DEFAULT NULL,
    `category`     VARCHAR(32) NOT NULL,
    `link_href`    VARCHAR(64) NULL DEFAULT NULL,
    `link_title`   VARCHAR(64) NULL DEFAULT NULL
);

CREATE TABLE `stream`
(
    `stream_id` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `name`      VARCHAR(32) NOT NULL
);

CREATE TABLE `dependence`
(
    `dependence_id` INTEGER PRIMARY KEY AUTO_INCREMENT,
    `parent`        INTEGER NOT NULL,
    `child`         INTEGER NOT NULL,
    FOREIGN KEY (`child`) REFERENCES `stream` (`stream_id`),
    FOREIGN KEY (`parent`) REFERENCES `stream` (`stream_id`)
);

CREATE TABLE `priority`
(
    `priority_id`   INTEGER PRIMARY KEY AUTO_INCREMENT,
    `news_id`       INTEGER NOT NULL,
    `stream_id`     INTEGER NOT NULL,
    `priority`      INTEGER NOT NULL DEFAULT 0,
    `priority_from` INTEGER NULL     DEFAULT NULL,
    `priority_to`   INTEGER NULL     DEFAULT NULL,
    FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`),
    FOREIGN KEY (`stream_id`) REFERENCES `stream` (`stream_id`)
);


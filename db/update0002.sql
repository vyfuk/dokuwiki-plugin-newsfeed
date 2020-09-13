CREATE TABLE news
(
    'news_id'      INTEGER PRIMARY KEY AUTOINCREMENT,
    'title'        TEXT NOT NULL,
    'author_name'  TEXT NOT NULL,
    'author_email' TEXT NOT NULL,
    'text'         TEXT NOT NULL,
    'news_date'    TEXT NOT NULL,
    'image'        TEXT NULL DEFAULT NULL,
    'category'     TEXT NOT NULL,
    'link_href'    TEXT NULL DEFAULT NULL,
    'link_title'   TEXT NULL DEFAULT NULL
);

CREATE TABLE stream
(
    'stream_id' INTEGER PRIMARY KEY AUTOINCREMENT,
    'name'      VARCHAR
);

CREATE TABLE dependence
(
    'dependence_id' INTEGER PRIMARY KEY AUTOINCREMENT,
    'parent'        INTEGER,
    'child'         INTEGER,
    FOREIGN KEY (child) REFERENCES stream (stream_id),
    FOREIGN KEY (parent) REFERENCES stream (stream_id)
);

CREATE TABLE priority
(
    'priority_id'   INTEGER PRIMARY KEY AUTOINCREMENT,
    'news_id'       INTEGER,
    'stream_id'     INTEGER,
    'priority'      INTEGER,
    'priority_from' INTEGER,
    'priority_to'   INTEGER,
    FOREIGN KEY (news_id) REFERENCES news (news_id),
    FOREIGN KEY (stream_id) REFERENCES stream (stream_id)
);


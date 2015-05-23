
CREATE TABLE fks_newsfeed_news(
'id' INTEGER PRIMARY KEY AUTOINCREMENT,
'name' VARCHAR,
'author' VARCHAR,
'email' VARCHAR,
'text' TEXT,
'newsdate' TEXT,
'image' VARCHAR
);

CREATE TABLE fks_newsfeed_dependence(
'id' INTEGER PRIMARY KEY AUTOINCREMENT,
'dependence_from' VARCHAR,
'dependence_to' VARCHAR
);

CREATE TABLE fks_newsfeed_order(
'id' INTEGER PRIMARY KEY AUTOINCREMENT,
'news_id' INTEGER,
'stream' VARCHAR,
'weight' int,
 FOREIGN KEY(news_id) REFERENCES fks_newsfeed_news(id)
);




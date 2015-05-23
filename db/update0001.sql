CREATE TABLE fks_newsfeed_news(
'news_id' INTEGER PRIMARY KEY AUTOINCREMENT,
'name' VARCHAR,
'author' VARCHAR,
'email' VARCHAR,
'text' TEXT,
'newsdate' TEXT,
'image' VARCHAR
);

CREATE TABLE fks_newsfeed_stream(
'stream_id' INTEGER PRIMARY KEY AUTOINCREMENT,
'name' VARCHAR
);

CREATE TABLE fks_newsfeed_dependence(
'dependence_id' INTEGER PRIMARY KEY AUTOINCREMENT,
'dependence_from' INTEGER,
'dependence_to' INTEGER,
FOREIGN KEY(dependence_from) REFERENCES fks_newsfeed_stream(stream_id),
FOREIGN KEY(dependence_to) REFERENCES fks_newsfeed_stream(stream_id)
);

CREATE TABLE fks_newsfeed_order(
'order_id' INTEGER PRIMARY KEY AUTOINCREMENT,
'news_id' INTEGER,
'stream_id' INTEGER,
'weight' INTEGER,
FOREIGN KEY(news_id) REFERENCES fks_newsfeed_news(news_id),
FOREIGN KEY(stream_id) REFERENCES fks_newsfeed_stream(stream_id)
);

CREATE VIEW v_dependence AS
select s_from.name as 'dependence_from' ,s_to.name as 'dependence_to' 
from fks_newsfeed_dependence  d
join fks_newsfeed_stream s_from ON d.dependence_from=s_from.stream_id 
join fks_newsfeed_stream s_to ON d.dependence_to=s_to.stream_id 
;




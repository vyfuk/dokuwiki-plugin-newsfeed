<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('DOKU_INC'))
    define('DOKU_INC', dirname(__FILE__) . '/../../../');
require_once(DOKU_INC . 'inc/init.php');

session_write_close();


header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

if (!actionOK('rss')) {
    http_status(404);
    echo '<error>RSS feed is disabled.</error>';
    exit;
}





$rss = new DokuWikiFeedCreator();
$rss->title = $conf['title'];

$rss->link = DOKU_URL;
$rss->syndicationURL = DOKU_URL . 'lib/plugins/fksnewsfeed/rss.php';
$rss->cssStyleSheet = DOKU_URL . 'lib/exe/css.php?s=feed';


$rss->image = $image;
global $INPUT;
if (empty($INPUT->str('stream'))) {
    exit('<error>RSS feed is disabled.</error>');
}

foreach (helper_plugin_fksnewsfeed::loadstream($INPUT->str('stream')) as $value) {

    
    $helper= new helper_plugin_fksnewsfeed;
    $url = metaFN($helper->getwikinewsurl($value), '.txt');
    
    $ntext = io_readFile($url);
    
    $cleantext = str_replace(array("\n", '<fksnewsfeed', '</fksnewsfeed>'), array('', '', ''), $ntext);
    list($params, $text) = preg_split('/\>/', $cleantext, 2);
    $param = helper_plugin_fkshelper::extractParamtext($params);

    
    
    $data = new UniversalFeedCreator();
    $data->pubDate = $param['newsdate'];
    $data->title = $param['name'];
    $action = new action_plugin_fksnewsfeed();
    $data->link=$action->_generate_token($value);
    $data->description=$text;
    $data->editor=$param['author'];
    $data->editorEmail=$param['email'];
    $data->webmaster='miso@fykos.cz';
    $data->category=$INPUT->str('stream'); 
    /*
 */
    $rss->addItem($data);
}


//var_dump($rss)
;
echo $rss->createFeed($opt['feed_type'], 'utf-8');

class fksnewsfeedrss extends DokuWikiFeedCreator {

    public function FKS_newsfeed_add_item(DokuWikiFeedCreator $rss, $data) {

        //    title	The title of the item.	Venice Film Festival Tries to Quit Sinking
//link	The URL of the item.	http://www.nytimes.com/2002/09/07/movies/07FEST.html
//description	The item synopsis.	Some of the most heated chatter at the Venice Film Festival this week was about the way that the arrival of the stars at the Palazzo del Cinema was being staged.
//author	Email address of the author of the item. More.	oprah@oxygen.net
//category	Includes the item in one or more categories. More.	Simpsons Characters
//comments	URL of a page for comments relating to the item. More.	http://www.myblog.org/cgi-local/mt/mt-comments.cgi?entry_id=290
//enclosure	Describes a media object that is attached to the item. More.	<enclosure url="http://live.curry.com/mp3/celebritySCms.mp3" length="1069871" type="audio/mpeg"/>
//guid	A string that uniquely identifies the item. More.	<guid isPermaLink="true">http://inessential.com/2002/09/01.php#a2</guid>
//pubDate	Indicates when the item was published. More.	Sun, 19 May 2002 15:21:36 GMT
//source
    }

}

<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz> 
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

if(!defined('DOKU_PLUGIN')){
    define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
}

require_once(DOKU_PLUGIN.'admin.php');

class admin_plugin_fksnewsfeed_stream extends DokuWiki_Admin_Plugin {

    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksnewsfeed');
    }

    public function getMenuSort() {
        return 290;
    }

    public function forAdminOnly() {
        return false;
    }

    public function getMenuText() {
        $menutext = 'FKS_newsfeed: Streams --'.$this->getLang('menu_streams');
        return $menutext;
    }

    public function handle() {
        global $INPUT;
        $stream_name = $INPUT->str('stream_name');
        if($stream_name == ""){
            return;
        }
        if($this->helper->StreamToID($stream_name) == 0){
            $this->helper->CreateStream($stream_name);

            msg('Stream hes been created',1);
        }else{
            msg('Stream alredy exist',-1);
        }
    }

    function getTOC() {
        return array(
            array('hid' => 'stream_create','title' => $this->getLang('stream_create')),
            array('hid' => 'stream_list','title' => $this->getLang('stream_list')),
            array('hid' => 'stream_delete','title' => $this->getLang('stream_delete'))
        );
    }

    public function html() {
        global $lang;
        ptln('<h1>'.$this->getLang('manage').'</h1>',0);
        ptln('<h2 id="stream_create">'.'Create stream'.'</h2>',1);
        $form = new Doku_Form(array('id' => "create_stream",
            'method' => 'POST','action' => null));
        $form->addHidden('news_do','stream_add');
        $form->addElement(form_makeTextField('stream_name',null,'názov streamu'));
        $form->addElement(form_makeButton('submit','',$lang['btn_save'],array()));
        html_form('nic',$form);
        $streams = $this->helper->AllStream();
        ptln('<h2 id="stream_list">Zoznam Streamov</h2>',1);
        ptln('<ul>');
        foreach ($streams as $stream) {
            ptln('<li><span>'.$stream);
            ptln('<input type="text" class="edit" value="{{fksnewsfeed-stream>stream='.$stream.';feed=5}}" />');
            ptln('</span></li>');
        }
        ptln('</ul>');
        ptln('<h2 id="stream_delete">'.'Zmazať stream'.'</h2>');
        ptln('<div class="level2">');
        echo 'Ak chcete zmazať stream, prosím požite rozhranie <a href="'.DOKU_BASE.'?do=admin&page=sqlite">SQLite</a>.';
        ptln('<br />');
        echo 'Pred zmazaním sa uistite, že vočí tomuto streamu neexistú žiadné závylosť (rodičovské aj dedské). Tieto závyslosti treba zmazať pred mazaním streamu.';
        ptln('<br />');
        echo 'V opačnom pripade môže dôjsť k chybovému správaniu pluginu!!!';
        ptln('<br />');
        echo 'Zmazané prevedieťe pomocou príkazu, za <span class="grey">"name_of_stream"</span> doplnte názov streamu.';
        ptln('<div class="code">',7);
        ptln('<span class="blue">DELETE FROM </span>');
        ptln('<br />');
        ptln('<span class="red">"fks_newsfeed_stream" </span>');
        ptln('<br />');
        ptln('<span class="blue">WHERE </span>');
        ptln('<br />');
        ptln('<span class="green">name </span>= <span class="grey">"name_of_stream"</span>;');
        ptln('</div>');
        ptln('</div>');
    }

}

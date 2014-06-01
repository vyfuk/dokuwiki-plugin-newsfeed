<?php

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeed_fksnewsfeed extends DokuWiki_Syntax_Plugin {

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'normal';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksnewsfeed>.+?\}\}', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        $match = substr($match, 14, -2);
        $match+=0;
        $to_page.="<div class='fksnewswrapper'>";
        $imax;
        for ($i = 1; true; $i++) {
            $newsurl = getnewsurl($ri, 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt');
            if (file_exists($newsurl)) {
                continue;
            } else {
                $imax = $i - 1;
                break;
            }
        }
        $rendernews = preg_split('/;/', io_readFile("data/meta/newsfeed.csv", FALSE));
        for ($i = 0; $i < count($rendernews); $i++) {
            $rendernewsbool = preg_split('/-/', $rendernews[$i]);
            if ($rendernewsbool[1] == "T") {
                if ($match) {
                    $newsurl = getnewsurl($rendernewsbool[0], 'data/pages/' . $this->getConf('newsfolder') . '/' . $this->getConf('newsfile') . '.txt');
                    $to_page.=rendernews($match, $newsurl);
                } else {
                    break;
                }
            }
        }
        $to_page.="</div>";
        return array($state, array($to_page));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
        // $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($to_page) = $match;
            $renderer->doc .= $to_page;
        }
        return false;
    }

}

function getnewsurl($newsno, $newsurl) {
    $newsurl = str_replace("@i@", $newsno, $newsurl);
    //if (file_exists($newsurl)) {
    return $newsurl;
}

function rendernews($match, $newsurl) {
    if (file_exists($newsurl)) {
        $match--;
        if ($match % 2) {
            /*
             * find even and odd news, css is not same.
             */
            $to_page.="<div class='fksnewseven'>";
        } else {
            $to_page.="<div class='fksnewsodd'>";
        }
        /*
         * find news autor title and date news and render then
         */
        $newsdata = io_readFile($newsurl, false);
        $newsfeed = preg_split('/====/', $newsdata);
        $newsdate = preg_split('/newsdate/', $newsdata);
        $newsauthor = preg_split('/newsauthor/', $newsdata);

        $to_page.= p_render("xhtml", p_get_instructions('<newsdate>' . substr($newsdate[1], 1, -2) . '-render</newsdate>'), $info);

        $to_page.='<div class="fksnewsheadline">';
        $to_page.= p_render("xhtml", p_get_instructions('===' . $newsfeed[1] . '==='), $info);
        $to_page.="</div>";

        $to_page.='<div class="fksnewsarticle">';
        $to_page.= p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
        $to_page.="</div>";
        $to_page.= p_render("xhtml", p_get_instructions('<newsauthor>' . substr($newsauthor[1], 1, -2) . '-render</newsauthor>'), $info);
        $to_page.='<div class="clearer"></div>';
        $to_page.="</div>";
        $to_page.='<div class="clearer"></div>';
        return $to_page;
    }
}

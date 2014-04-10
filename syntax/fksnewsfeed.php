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
        $this->Lexer->addSpecialPattern('<fksnewsfeed>.+?</fksnewsfeed>', $mode, 'plugin_fksnewsfeed_fksnewsfeed');
    }

    //public function postConnect() { $this->Lexer->addExitPattern('</fkstimer>','plugin_fkstimer'); }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        $noi = 1;
        for (; $noi < 20; $noi++) {
            $matchpar = substr($match, 14 + $noi, 1);
            /* @var $matchpar patr of $match */
            if ($matchpar === "<") {
                break;
            }
        }
        $match = substr($match, 14, $noi);
        $match+=0;
        $to_page.="<div class='fksnewswrapper'>";
        for ($i = 2000; $i > 0; $i--) {
            if ($match) {
                /**
                 * find news wiht max number
                 */
                if (file_exists("data/pages/fksnewsfeed/news" . $i . ".txt")) {
                    $match--;
                    if ($match % 2) {
                        $to_page.="<div class='fksnewseven'>";
                    } else {
                        $to_page.="<div class='fksnewsodd'>";
                    }
                    $newsfeed = preg_split('/====/', io_readFile("data/pages/fksnewsfeed/news" . $i . ".txt", false));
                    $newsdate = preg_split('/newsdate/', io_readFile("data/pages/fksnewsfeed/news" . $i . ".txt", false));
                    $newsauthor = preg_split('/newsauthor/', io_readFile("data/pages/fksnewsfeed/news" . $i . ".txt", false));
                    
                    $to_page.= p_render("xhtml", p_get_instructions('<newsdate>' . substr($newsdate[1], 1, -2). '-render</newsdate>'), $info);
                    
                    $to_page.='<div class="fksnewsheadline">';
                    $to_page.= p_render("xhtml", p_get_instructions('===' . $newsfeed[1] . '==='), $info);
                    $to_page.="</div>";
                    
                    
                    $to_page.='<div class="fksnewsarticle">';
                    $to_page.= p_render("xhtml", p_get_instructions($newsfeed[2]), $info);
                    $to_page.="</div>";
                    $to_page.= p_render("xhtml", p_get_instructions('<newsauthor>' . substr($newsauthor[1], 1, -2). '-render</newsauthor>'), $info);
                    $to_page.='<div class="clearer"></div>';
                    $to_page.="</div>";
                    $to_page.='<div class="clearer"></div>';
                    //$to_page.="</div>";
                }
            } else {
                break;
            }
        }
        $to_page.="</div>";

        //$feedsdata = io_readFile("data/pages/fksnewsfeed.txt", false);
        /* @var $feedsdata ArrayObject */
        //$feedsdata = fksnewsfeed($feedsdata, $match);
        /* $imax = 2 * $match + 1;
          $jmax = strlen($feedsdata);
          $i = 0;
          for (; $i < $jmax - 3; $i++) {
          $datapar = substr($feedsdata, $i, 4);
          if ($datapar == '====') {
          $imax--;
          }
          if (!$imax) {
          break;
          }
          }
          $feedsdata = substr($feedsdata, 0, $i);
          $to_page = p_render("xhtml", p_get_instructions($feedsdata), $info);
         */
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

    private function fksnewsfeed(&$feedsdata, $feedsno) {

        return $feedsdata;
    }

}

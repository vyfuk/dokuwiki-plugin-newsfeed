<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki

if (!defined('DOKU_INC')) {
    die();
}
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_fksnewsfeed_newsdate extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('<newsdate>.+?</newsdate>', $mode, 'plugin_fksnewsfeed_newsdate');
    }

    //public function postConnect() { $this->Lexer->addExitPattern('</fkstimer>','plugin_fkstimer'); }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {
        $match = substr($match, 10, -11);
        $newsdate = preg_split('/-/', $match);
        if ($newsdate[1] == 'render') {
            $enmonth = Array(
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
                'December');
            $langmonth = Array(
                $this->getLang('jan'),
                $this->getLang('feb'),
                $this->getLang('mar'),
                $this->getLang('apr'),
                $this->getLang('may'),
                $this->getLang('jun'),
                $this->getLang('jul'),
                $this->getLang('aug'),
                $this->getLang('sep'),
                $this->getLang('oct'),
                $this->getLang('now'),
                $this->getLang('dec')
            );

            $to_page.="<div class='fksnewsdate'>";
            $newsdate[0]=str_replace($enmonth, $langmonth, $newsdate[0]);
            $to_page.=$newsdate[0];
            $to_page.="</div>";
            return array($state, array($to_page));
        } else {
            
        };
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

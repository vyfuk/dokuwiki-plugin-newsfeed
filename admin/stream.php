<?php

use dokuwiki\Extension\AdminPlugin;

/**
 * Class admin_plugin_newsfeed_stream
 * @author Michal Červeňák <miso@fykos.cz>
 */
class admin_plugin_newsfeed_stream extends AdminPlugin {

    private helper_plugin_newsfeed $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('newsfeed');
    }

    public function getMenuSort(): int {
        return 290;
    }

    public function forAdminOnly(): bool {
        return false;
    }

    public function getMenuText($lang): string {
        return $this->getLang('stream_menu');
    }

    public function html(): void {
        echo '<h1>' . $this->getLang('stream_list') . '</h1>';
        $streams = $this->helper->getAllStreams();
        echo('<ul>');
        foreach ($streams as $stream) {
            echo '<li class="form-group row"><span class="col-3">' . $stream->getName() . '</span>';
            echo '<input type="text" class="col-9 form-control" value="' .
                hsc('{{news-stream>stream="' . $stream->getName() . '" feed="5"}}') . '" />';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

}

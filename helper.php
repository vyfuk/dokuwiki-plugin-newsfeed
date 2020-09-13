<?php

require_once __DIR__ . '/inc/ORM/AbstractModel.php';
require_once __DIR__ . '/inc/ORM/ModelNews.php';
require_once __DIR__ . '/inc/ORM/ModelPriority.php';
require_once __DIR__ . '/inc/ORM/ModelStream.php';
require_once __DIR__ . '/inc/ORM/ModelDependence.php';
require_once __DIR__ . '/inc/ORM/AbstractService.php';
require_once __DIR__ . '/inc/ORM/ServiceDependence.php';
require_once __DIR__ . '/inc/ORM/ServiceNews.php';
require_once __DIR__ . '/inc/ORM/ServicePriority.php';
require_once __DIR__ . '/inc/ORM/ServiceStream.php';

require_once __DIR__ . '/inc/renderer/AbstractRenderer.php';
require_once __DIR__ . '/inc/renderer/VyfukRenderer.php';
require_once __DIR__ . '/inc/renderer/FykosRenderer.php';
require_once __DIR__ . '/inc/AbstractStream.php';
require_once __DIR__ . '/../social/inc/OpenGraphData.php';

use dokuwiki\Extension\Plugin;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServiceDependence;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServiceNews;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServicePriority;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\ORM\ServiceStream;
use FYKOS\dokuwiki\Extension\PluginSocial\OpenGraphData;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelNews;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Model\ModelStream;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Renderer\AbstractRenderer;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Renderer\FykosRenderer;
use FYKOS\dokuwiki\Extension\PluginNewsFeed\Renderer\VyfukRenderer;

class helper_plugin_newsfeed extends Plugin {

    const FORM_TARGET = 'plugin_newsfeed';

    public static array $fields = [
        'title',
        'authorName',
        'authorEmail',
        'newsDate',
        'image',
        'category',
        'linkHref',
        'linkTitle',
        'text',
    ];
    public helper_plugin_sqlite $sqlite;

    public OpenGraphData $openGraphData;

    public AbstractRenderer $renderer;


    public ServiceNews $serviceNews;
    public ServicePriority $servicePriority;
    public ServiceDependence $serviceDependence;
    public ServiceStream $serviceStream;

    public function __construct() {

        $this->openGraphData = new OpenGraphData();
        $this->sqlite = $this->loadHelper('sqlite');

        $this->serviceNews = new ServiceNews($this->sqlite);
        $this->servicePriority = new ServicePriority($this->sqlite);
        $this->serviceDependence = new ServiceDependence($this->sqlite);
        $this->serviceStream = new ServiceStream($this->sqlite);

        $pluginName = $this->getPluginName();
        if (!$this->sqlite) {
            msg($pluginName . ': This plugin requires the sqlite plugin. Please install it.');
        }

        if (!$this->sqlite->init('newsfeed', DOKU_PLUGIN . $pluginName . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR)
        ) {
            msg($pluginName . ': Cannot initialize database.');
        }

        switch ($this->getConf('contest')) {
            default:
            case 'fykos':
                $this->renderer = new FykosRenderer($this);
                break;
            case 'vyfuk':
                $this->renderer = new VyfukRenderer($this);
                break;
        }
    }

    /**
     * @param $streamId integer
     * @return integer[]
     */
    private function allParentDependence(int $streamId): array {
        $streamIds = [];
        $res = $this->sqlite->query('SELECT * FROM dependence WHERE parent=?', $streamId);
        foreach ($this->sqlite->res2arr($res) as $row) {
            $streamIds[] = $row['child'];
        }
        return $streamIds;
    }

    /**
     * @param $streamId integer
     * @param array $arr
     * @return void
     */
    public function fullParentDependence(int $streamId, array &$arr): void {
        foreach ($this->allParentDependence($streamId) as $newStreamId) {
            if (!in_array($newStreamId, $arr)) {
                $arr[] = $newStreamId;
                $this->fullParentDependence($newStreamId, $arr);
            }
        }
    }
}

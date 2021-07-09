<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/27
 * Time: 5:45 PM
 */

namespace common\widgets\gmap;

use backend\modules\conf\settings\GoogleMapSettings;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

class SingleViewWidget extends Widget
{
    /**
     * html options of the map wrapper
     * @var array
     */
    public $mapWrapperHtmlOptions = [];


    /**
     * @var string
     */
    public $latitude;
    /**
     * @var
     */
    public $longitude;

    /**
     * @var string
     */
    public $infowindowContent;

    /**
     * @var int
     */
    public $zoom;

    /**
     * @var string
     */
    public $mapType;

    /**
     * @var bool
     */
    public $panControl = true;
    /**
     * @var bool
     */
    public $zoomControl = true;
    /**
     * @var bool
     */
    public $scaleControl = true;
    /**
     * @var string
     */
    public $markerColor = 'FF0000';

    /**
     * lat,lng centre of the map
     * @var string
     */
    private $mapCentre;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->mapWrapperHtmlOptions['id'])) {
            $this->mapWrapperHtmlOptions['id'] = 'gmap-single-view-xx';
        }
        if (empty($this->latitude) || empty($this->longitude)) {
            $this->mapCentre = GoogleMapSettings::getDefaultMapCenter();
            if (!empty($this->mapCentre)) {
                $center = explode(',', $this->mapCentre);
                $this->latitude = $center[0];
                $this->longitude = isset($center[1]) ? $center[1] : NULL;
            }
        }
        if (empty($this->zoom)) {
            $this->zoom = GoogleMapSettings::getDefaultSingleViewZoom();
        }

        if (empty($this->mapType))
            $this->mapType = GmapUtils::MAP_TYPE_HYBRID;
    }

    public function run()
    {
        echo Html::tag('div', "", $this->mapWrapperHtmlOptions);
        $this->registerAssets();
    }

    protected function registerAssets()
    {
        $view = $this->getView();
        $map_js = 'https://maps.googleapis.com/maps/api/js?key=' . GoogleMapSettings::getApiKey();
        $view->registerJsFile($map_js);
        AssetBundle::register($view);

        $options = [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'mapWrapperId' => $this->mapWrapperHtmlOptions['id'],
            'infowindowContent' => $this->infowindowContent,
            'zoom' => $this->zoom,
            'mapType' => $this->mapType,
            'panControl' => $this->panControl,
            'zoomControl' => $this->zoomControl,
            'scaleControl' => $this->scaleControl,
            'markerColor' => $this->markerColor,
        ];
        $view->registerJs("MyApp.gmap.singleView(" . Json::encode($options) . ");");
    }


}
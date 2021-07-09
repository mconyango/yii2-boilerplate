<?php

/**
 * A google map showing many locations with markers
 *
 * @author Fred <mconyango@gmail.com>
 */
class GmapCrowdMap extends CWidget {

        /**
         * 2D array of locations
         * @var array
         */
        public $data = array();

        /**
         * Template for displaying info window
         * placeholders should correspond to the db fields
         * e.g<pre>"p{{name}}p"</pre>
         * Uses Handlebars to process the template
         * @link https://github.com/wycats/handlebars.js/ handlebars plugins github page
         * @var string
         */
        public $infowindow_content_template = '';

        /**
         * Latitude field
         * @var type
         */
        public $lat_field = 'latitude';

        /**
         * Longitude field
         * @var type
         */
        public $lng_field = 'longitude';

        /**
         * html options of the map wrapper
         * @var type
         */
        public $map_wrapper_htmlOptions = array();

        /**
         * lat,lng centre of the map
         * @var type
         */
        private $map_centre;
        public $lat;
        public $lng;

        public function init() {
                if (empty($this->map_wrapper_htmlOptions['id'])) {
                        $this->map_wrapper_htmlOptions['id'] = 'my_gmap_crowd_map';
                }

                if (empty($this->lat) || empty($this->lng)) {
                        $this->map_centre = Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_DEFAULT_CENTER);
                        if (!empty($this->map_centre)) {
                                $center = explode(',', $this->map_centre);
                                $this->lat = $center[0];
                                $this->lng = isset($center[1]) ? $center[1] : NULL;
                        }
                }

                parent::init();
        }

        public function run() {
                echo CHtml::tag('div', $this->map_wrapper_htmlOptions, "", true);
                $this->registerAssets();
        }

        protected function registerAssets() {
                $assets = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
                $baseUrl = Yii::app()->assetManager->publish($assets);
                $options = CJavaScript::encode(array(
                            'lat' => $this->lat,
                            'lng' => $this->lng,
                            'data' => $this->data,
                            'infowindow_content_template' => $this->infowindow_content_template,
                            'map_wrapper_id' => $this->map_wrapper_htmlOptions['id'],
                            'zoom' => (int) Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_CROWD_MAP_ZOOM),
                ));
                Yii::app()->clientScript
                        ->registerScriptFile('http://maps.googleapis.com/maps/api/js?key=' . Yii::app()->settings->get(SettingsModuleConstants::SETTINGS_GOOGLE_MAP, SettingsModuleConstants::SETTINGS_GOOGLE_MAP_API_KEY) . '&sensor=false', CClientScript::POS_END)
                        ->registerScriptFile($baseUrl . '/js/crowdmap.js', CClientScript::POS_END)
                        ->registerScript(microtime(), "MyGmapCrowdMap.init(" . $options . ");", CClientScript::POS_READY);
        }

}

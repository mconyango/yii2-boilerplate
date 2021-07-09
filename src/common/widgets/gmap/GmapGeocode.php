<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-06-02 12:52 PM
 */

namespace common\widgets\gmap;

use backend\modules\conf\settings\GoogleMapSettings;
use common\helpers\Lang;
use common\models\ActiveRecord;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Html;
use yii\helpers\Json;

class GmapGeocode extends Widget
{
    /**
     * Model associated with the geocode
     * @var ActiveRecord
     */
    public $model;

    /**
     * geocode url
     * @var string
     */
    public $geocodeUrl;

    /**
     * lat,lng centre of the map
     * @var string
     */
    private $_mapCentre;
    /**
     * @var string
     */
    public $latitude;
    /**
     * @var string
     */
    public $longitude;

    /**
     *
     * @var string
     */
    public $latitudeAttribute = 'latitude';

    /**
     *
     * @var string
     */
    public $longitudeAttribute = 'longitude';

    /**
     *
     * @var string
     */
    public $addressAttribute = 'location';

    /**
     *
     * @var string
     */
    public $template = '<div class="form-group">{{latitudeField}}{{longitudeField}}</div>{{map}}<div class="form-group"><br/>{{addressField}}</div>';

    /**
     * Template for latitude field
     * @var string
     */
    public $latitudeFieldTemplate = '<div class="col-sm-6">{{label}}{{input}}</div>';

    /**
     *
     * @var array
     */
    public $latitudeInputHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $latitudeLabelHtmlOptions = [];

    /**
     *
     * @var bool
     */
    public $showLatitudeLabel = false;

    /**
     * Template for longitude field
     * @var string
     */
    public $longitudeFieldTemplate = '<div class="col-sm-6">{{label}}{{input}}</div>';

    /**
     *
     * @var array
     */
    public $longitudeInputHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $longitudeLabelHtmlOptions = [];

    /**
     *
     * @var bool
     */
    public $showLongitudeLabel = false;

    /**
     * html options of the map wrapper
     * @var array
     */
    public $mapWrapperHtmlOptions = [];

    /**
     * address field template
     * @var string
     */
    public $addressFieldTemplate = '{{label}}<div class="col-sm-7">{{input}}</div><div class="col-sm-2">{{search_button}}</div>';

    /**
     *
     * @var array
     */
    public $addressInputHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $addressLabelHtmlOptions = [];

    /**
     *
     * @var array
     */
    public $addressSearchFieldHtmlOptions = [];

    /**
     *
     * @var bool
     */
    public $showAddressLabel = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //latitude field html options
        if (empty($this->latitudeInputHtmlOptions['class'])) {
            $this->latitudeInputHtmlOptions['class'] = 'form-control';
        }
        if (!$this->showLatitudeLabel) {
            $this->latitudeInputHtmlOptions['placeholder'] = $this->model->getAttributeLabel($this->latitudeAttribute);
        }
        $this->latitudeInputHtmlOptions['readonly'] = true;
        //longitude field html options
        if (empty($this->longitudeInputHtmlOptions['class'])) {
            $this->longitudeInputHtmlOptions['class'] = 'form-control';
        }
        if (!$this->showLongitudeLabel) {
            $this->longitudeInputHtmlOptions['placeholder'] = $this->model->getAttributeLabel($this->longitudeAttribute);
        }
        $this->longitudeInputHtmlOptions['readonly'] = true;

        //address field html options
        if (empty($this->addressInputHtmlOptions['class'])) {
            $this->addressInputHtmlOptions['class'] = 'form-control';
        }
        if (empty($this->addressInputHtmlOptions['placeholder'])) {
            $this->addressInputHtmlOptions['placeholder'] = Lang::t('Search location on the map.');
        }
        if ($this->showAddressLabel) {
            if (empty($this->addressLabelHtmlOptions['class']))
                $this->addressLabelHtmlOptions['class'] = 'col-sm-2 control-label';
        }
        if (empty($this->addressSearchFieldHtmlOptions['class']))
            $this->addressSearchFieldHtmlOptions['class'] = 'btn btn-sm btn-default';
        $this->addressSearchFieldHtmlOptions['type'] = 'button';
        $this->addressSearchFieldHtmlOptions['id'] = 'my-geocode-address-search';

        if (empty($this->mapWrapperHtmlOptions['id'])) {
            $this->mapWrapperHtmlOptions['id'] = 'my-gmap-geocode';
        }

        if (empty($this->latitude) || empty($this->longitude)) {
            $this->_mapCentre = GoogleMapSettings::getDefaultMapCenter();
        }
        if (!empty($this->_mapCentre)) {
            $center = explode(',', $this->_mapCentre);
            $this->latitude = trim($center[0]);
            $this->longitude = isset($center[1]) ? trim($center[1]) : null;
        }
    }

    public function run()
    {
        //latitude
        $latitude_label = '';
        if ($this->showLatitudeLabel) {
            $latitude_label = Html::activeLabel($this->model, $this->latitudeAttribute, $this->latitudeLabelHtmlOptions);
        }
        $latitude_input = Html::activeTextInput($this->model, $this->latitudeAttribute, $this->latitudeInputHtmlOptions);
        $lat = strtr($this->latitudeFieldTemplate, [
            '{{label}}' => $latitude_label,
            '{{input}}' => $latitude_input,
        ]);
        //longitude
        $longitude = '';
        if ($this->showLongitudeLabel) {
            $longitude = Html::activeLabel($this->model, $this->longitudeAttribute, $this->longitudeLabelHtmlOptions);
        }
        $longitude_field = Html::activeTextInput($this->model, $this->longitudeAttribute, $this->longitudeInputHtmlOptions);
        $lng = strtr($this->longitudeFieldTemplate, [
            '{{label}}' => $longitude,
            '{{input}}' => $longitude_field,
        ]);

        //address
        $address_label = '';
        if ($this->showAddressLabel) {
            $address_label = Html::activeLabel($this->model, $this->addressAttribute, $this->addressLabelHtmlOptions);
        }
        $address_field = Html::activeTextInput($this->model, $this->addressAttribute, $this->addressInputHtmlOptions);
        $search_button = Html::tag('button', Html::tag('i', '', ['class' => 'fa fa-search']) . ' ' . Lang::t('Search'), $this->addressSearchFieldHtmlOptions);
        $address = strtr($this->addressFieldTemplate, [
            '{{label}}' => $address_label,
            '{{input}}' => $address_field,
            '{{search_button}}' => $search_button,
        ]);

        $map_wrapper = Html::tag('div', "", $this->mapWrapperHtmlOptions);
        //<div class="form-group">{{latitudeField}}{{longitudeField}}</div>{{map}}<div class="form-group">{{addressField}}</div>
        $html = strtr($this->template, [
            '{{latitudeField}}' => $lat,
            '{{longitudeField}}' => $lng,
            '{{map}}' => $map_wrapper,
            '{{addressField}}' => $address,
        ]);

        echo $html;

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
            'geocodeUrl' => $this->geocodeUrl,
            'modelClass' => strtolower($this->model->shortClassName()),
            'latitudeAttribute' => $this->latitudeAttribute,
            'longitudeAttribute' => $this->longitudeAttribute,
            'addressAttribute' => $this->addressAttribute,
            'mapWrapperId' => $this->mapWrapperHtmlOptions['id'],
            'addressSearchFieldId' => $this->addressSearchFieldHtmlOptions['id'],
            'zoom' => (int)GoogleMapSettings::getDefaultSingleViewZoom(),
        ];
        $view->registerJs("MyApp.gmap.geocode(" . Json::encode($options) . ");");
    }
}
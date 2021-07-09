<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-10 17:13
 * Time: 17:13
 */

namespace backend\modules\conf\settings;


use common\helpers\Lang;

class GoogleMapSettings extends BaseSettings
{
    //google map
    const SECTION_GOOGLE_MAP = 'googleMap';
    const KEY_API_KEY = 'apiKey';
    const KEY_DEFAULT_MAP_CENTER = 'defaultMapCenter';
    const KEY_DEFAULT_MAP_TYPE = 'defaultMapType';
    const KEY_DEFAULT_CROWD_MAP_ZOOM = 'defaultCrowdMapZoom';
    const KEY_DEFAULT_SINGLE_VIEW_ZOOM = 'defaultSingleViewZoom';

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var string
     */
    public $defaultMapCenter;

    /**
     * @var string
     */
    public $defaultMapType;
    /**
     * @var string
     */
    public $defaultCrowdMapZoom;
    /**
     * @var string
     */
    public $defaultSingleViewZoom;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [self::KEY_API_KEY],
                'required',
            ],
            [
                [self::KEY_DEFAULT_MAP_TYPE, self::KEY_DEFAULT_MAP_CENTER, self::KEY_DEFAULT_CROWD_MAP_ZOOM, self::KEY_DEFAULT_SINGLE_VIEW_ZOOM],
                'safe',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            self::KEY_API_KEY => Lang::t('API Key'),
            self::KEY_DEFAULT_MAP_CENTER => Lang::t('Default Map Center'),
            self::KEY_DEFAULT_MAP_TYPE => Lang::t('Default Map Type'),
            self::KEY_DEFAULT_CROWD_MAP_ZOOM => Lang::t('Default Crowd Map Zoom'),
            self::KEY_DEFAULT_SINGLE_VIEW_ZOOM => Lang::t('Default Single View Zoom'),
        ];
    }

    /**
     * @return string
     */
    public static function getApiKey()
    {
        return static::getSettingsComponent()->get(self::SECTION_GOOGLE_MAP, self::KEY_API_KEY, 'AIzaSyAwQJXjzQ82D6nQsjwHHYZ1T6tDlRJe220');
    }

    /**
     * @return string
     */
    public static function getDefaultMapCenter()
    {
        return static::getSettingsComponent()->get(self::SECTION_GOOGLE_MAP, self::KEY_DEFAULT_MAP_CENTER, '-1.2920659, 36.82194619999996');
    }

    /**
     * @return string
     */
    public static function getDefaultMapType()
    {
        return static::getSettingsComponent()->get(self::SECTION_GOOGLE_MAP, self::KEY_DEFAULT_MAP_TYPE);
    }

    /**
     * @return string
     */
    public static function getDefaultCrowdMapZoom()
    {
        return static::getSettingsComponent()->get(self::SECTION_GOOGLE_MAP, self::KEY_DEFAULT_CROWD_MAP_ZOOM);
    }

    /**
     * @return string
     */
    public static function getDefaultSingleViewZoom()
    {
        return static::getSettingsComponent()->get(self::SECTION_GOOGLE_MAP, self::KEY_DEFAULT_SINGLE_VIEW_ZOOM,10);
    }
}
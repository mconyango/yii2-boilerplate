<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-06 18:49
 * Time: 18:49
 */

namespace backend\modules\conf;


use common\models\ActiveRecord;

class Settings extends ActiveRecord
{
    use SettingsTrait;

    public $settingTable = 'conf_setting';

    /**
     * Pagination: number of items per page
     * @return integer
     */
    public static function pageSize()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_ITEMS_PER_PAGE, 50);
    }

    /**
     * @return string
     */
    public static function defaultTimezone()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_DEFAULT_TIMEZONE, date_default_timezone_get());
    }

    /**
     * Get the app name
     * @return string
     */
    public static function appName()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_APP_NAME, Yii::$app->name);
    }

    /**
     * Get the app name
     * @return string
     */
    public static function companyName()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_COMPANY_NAME, Yii::$app->name);
    }

    /**
     * Get default system currency
     * @return mixed
     */
    public static function defaultCurrency()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_CURRENCY, 'KES');
    }

    /**
     * Gets System default theme
     * @return integer
     */
    public static function defaultTheme()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_DEFAULT_THEME, self::THEME1);
    }

    /**
     * Gets System default country
     * @return integer
     */
    public static function defaultCountry()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(self::SECTION_SYSTEM, self::KEY_COUNTRY_ID);
    }

    /**
     * Get google map key
     * @return string
     */
    public static function googleMapKey()
    {
        /* @var $settings $this */
        $settings = Yii::$app->setting;
        return $settings->get(Settings::SECTION_GOOGLE_MAP, Settings::KEY_GOOGLE_MAP_API_KEY, 'AIzaSyAwQJXjzQ82D6nQsjwHHYZ1T6tDlRJe220');
    }

    /**
     * @param mixed $val
     * @return null|string
     */
    public static function decodeTheme($val)
    {
        $stringVal = null;
        switch ($val) {
            case self::THEME1:
                $stringVal = 'Theme 1';
                break;
            case self::THEME2:
                $stringVal = 'Theme 2';
                break;
            case self::THEME3:
                $stringVal = 'Theme 3';
                break;
        }

        return $stringVal;
    }

    /**
     * @return array
     */
    public static function themeOptions()
    {
        return [
            self::THEME1 => static::decodeTheme(self::THEME1),
            self::THEME2 => static::decodeTheme(self::THEME2),
            //self::THEME3 => static::decodeTheme(self::THEME3),
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-10 16:27
 * Time: 16:27
 */

namespace backend\modules\conf\settings;


use common\helpers\Lang;

class EmailSettings extends BaseSettings
{
    const SECTION_EMAIL = 'email';
    const KEY_HOST = 'host';
    const KEY_PORT = 'port';
    const KEY_USERNAME = 'username';
    const KEY_PASSWORD = 'password';
    const KEY_SECURITY = 'security';
    const KEY_THEME = 'theme';

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $port;
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $security;

    /**
     * @var string
     */
    public $theme;


    public function rules()
    {
        return [
            [
                [
                    self::KEY_HOST,
                    self::KEY_PORT,
                    self::KEY_USERNAME,
                    self::KEY_PASSWORD,
                    self::KEY_THEME,
                ],
                'required'
            ],
            [[self::KEY_PORT], 'integer'],
            [[self::KEY_SECURITY], 'safe']
        ];
    }


    public function attributeLabels()
    {
        return [
            self::KEY_HOST => Lang::t('Mail Host'),
            self::KEY_PORT => Lang::t('Mail Port'),
            self::KEY_USERNAME => Lang::t('Mail username'),
            self::KEY_PASSWORD => Lang::t('Mail password'),
            self::KEY_SECURITY => Lang::t('Mail security'),
            self::KEY_THEME => Lang::t('Mail Theme'),
        ];
    }

    /**
     * @return string
     */
    public static function getHost()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_HOST);
    }

    /**
     * @return string
     */
    public static function getPort()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_PORT);
    }

    /**
     * @return string
     */
    public static function getUsername()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_USERNAME);
    }

    /**
     * @return string
     */
    public static function getPassword()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_PASSWORD);
    }

    /**
     * @return string
     */
    public static function getSecurity()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_SECURITY);
    }

    /**
     * @return string
     */
    public static function getTheme()
    {
        return static::getSettingsComponent()->get(self::SECTION_EMAIL, self::KEY_THEME, '{{content}}');
    }
}
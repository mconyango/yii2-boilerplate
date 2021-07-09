<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-06 18:51
 * Time: 18:51
 */

namespace backend\modules\conf;


class SettingsConstants
{
    //system settings
    const SECTION_SYSTEM = 'system';
    const KEY_APP_NAME = 'app_name';
    const KEY_COMPANY_NAME = 'company_name';
    const KEY_COMPANY_EMAIL = 'company_email';
    const KEY_CURRENCY = 'currency_id';
    const KEY_ITEMS_PER_PAGE = 'items_per_page';
    const KEY_DEFAULT_TIMEZONE = 'default_timezone';
    const KEY_COUNTRY_ID = 'country_id';
    const KEY_DEFAULT_THEME = 'default_theme';
    //email settings
    const SECTION_EMAIL = 'email';
    const KEY_EMAIL_HOST = 'email_host';
    const KEY_EMAIL_PORT = 'email_port';
    const KEY_EMAIL_USERNAME = 'email_username';
    const KEY_EMAIL_PASSWORD = 'email_password';
    const KEY_EMAIL_SECURITY = 'email_security';
    const KEY_EMAIL_THEME = 'email_theme';
    //sms settings
    const SECTION_SMS = 'sms';
    const KEY_SMS_BASE_URL = 'sms_base_url';
    const KEY_SMS_SENDER_ID = 'sms_senser_id';
    const KEY_SMS_TRANSACTION_ID = 'sms_transaction_id';
    //google map
    const SECTION_GOOGLE_MAP = 'google_map';
    const KEY_GOOGLE_MAP_API_KEY = 'google_map_api_key';
    const KEY_GOOGLE_MAP_DEFAULT_CENTER = 'google_map_default_map_center';
    const KEY_GOOGLE_MAP_DEFAULT_MAP_TYPE = 'google_map_default_map_type';
    const KEY_GOOGLE_MAP_CROWD_MAP_ZOOM = 'google_map_crowd_map_zoom';
    const KEY_GOOGLE_MAP_SINGLE_VIEW_ZOOM = 'google_map_single_view_zoom';

    //themes
    const THEME1 = 'theme1';
    const THEME2 = 'theme2';
    const THEME3 = 'theme3';
}
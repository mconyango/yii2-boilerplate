<?php

namespace backend\modules\conf\settings;


use backend\modules\auth\Session;
use common\helpers\Lang;
use common\helpers\Utils;

class FinancialCalendarSettings extends BaseSettings
{
    const SECTION_FINANCIAL_CALENDAR = 'financial_calendar';
    const KEY_MAX_OPEN_CALENDAR = 'maxOpenCalendars';

    /**
     * @var int
     */
    public $maxOpenCalendars;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[self::KEY_MAX_OPEN_CALENDAR], 'required'],
            [['maxOpenCalendars'], 'integer', 'min' => 1, 'max' => 5]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            self::KEY_MAX_OPEN_CALENDAR => Lang::t('Maximum Open Calendars'),
        ];
    }

    /**
     * @return int
     */
    public static function getMaxOpenCalendars()
    {
        return (int)static::getSettingsComponent()->get(self::SECTION_FINANCIAL_CALENDAR, self::KEY_MAX_OPEN_CALENDAR, 1);
    }
}
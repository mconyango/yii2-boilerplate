<?php


namespace backend\modules\conf\controllers;


use backend\modules\conf\Constants;
use backend\modules\conf\settings\FinancialCalendarSettings;
use yii2mod\settings\actions\SettingsAction;

class FinancialCalendarSettingsController extends Controller
{
    public function init()
    {
        parent::init();
        $this->activeSubMenu = Constants::SUBMENU_FINANCIAL_CALENDAR;
        $this->resourceLabel = 'Financial Calendar Settings';
    }

    function actions()
    {
        return [
            'index' => [
                'class' => SettingsAction::class,
                'modelClass' => FinancialCalendarSettings::class,
                'sectionName' => FinancialCalendarSettings::SECTION_FINANCIAL_CALENDAR,
                'view' => 'index',
            ],
        ];
    }

}
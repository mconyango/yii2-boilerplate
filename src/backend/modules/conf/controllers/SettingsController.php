<?php

namespace backend\modules\conf\controllers;


use backend\modules\conf\settings\GoogleMapSettings;
use backend\modules\conf\settings\SystemSettings;
use yii2mod\settings\actions\SettingsAction;

class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->resourceLabel = 'Settings';
    }

    function actions()
    {
        return [
            'index' => [
                'class' => SettingsAction::class,
                'modelClass' => SystemSettings::class,
                'sectionName' => SystemSettings::SECTION_SYSTEM,
                'view' => 'index',
            ],
            'google-map' => [
                'class' => SettingsAction::class,
                'modelClass' => GoogleMapSettings::class,
                'sectionName' => GoogleMapSettings::SECTION_GOOGLE_MAP,
                'view' => 'google-map',
            ],
        ];
    }
}

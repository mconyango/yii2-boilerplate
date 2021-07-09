<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 28/10/18
 * Time: 22:50
 */

namespace backend\modules\conf\controllers;


use backend\modules\conf\Constants;

class SecuritySettingsController extends Controller
{
    public function init()
    {
        parent::init();
        $this->activeSubMenu = Constants::SUBMENU_SECURITY;
        $this->resourceLabel = 'Security Settings';
    }

    function actions()
    {
        return [
            'password' => [
                'class' => \yii2mod\settings\actions\SettingsAction::class,
                'modelClass' => \backend\modules\conf\settings\PasswordSettings::class,
                'sectionName' => \backend\modules\conf\settings\PasswordSettings::SECTION_PASSWORD,
                'view' => 'password',
            ],
        ];
    }

}
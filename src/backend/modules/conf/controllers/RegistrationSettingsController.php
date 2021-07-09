<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-01-18 12:18
 * Time: 12:18
 */

namespace backend\modules\conf\controllers;


use backend\modules\conf\Constants;

class RegistrationSettingsController extends Controller
{
    public function init()
    {
        parent::init();
        $this->activeSubMenu = Constants::SUBMENU_REGISTRATION;
        $this->resourceLabel = 'Registration Settings';
    }

    function actions()
    {
        return [
            'index' => [
                'class' => \yii2mod\settings\actions\SettingsAction::class,
                'modelClass' => \backend\modules\conf\settings\RegistrationSettings::class,
                'sectionName' => \backend\modules\conf\settings\RegistrationSettings::SECTION_MEMBER_REGISTRATION,
                'view' => 'index',
            ],
        ];
    }

}
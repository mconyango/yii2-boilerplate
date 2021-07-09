<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 5:59 PM
 */

namespace backend\modules\conf\controllers;

use backend\modules\conf\Constants;
use backend\modules\conf\models\EmailTemplate;

class EmailController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Email Template';
        $this->activeSubMenu = Constants::SUBMENU_EMAIL;
    }

    function actions()
    {
        return [
            'settings' => [
                'class' => \yii2mod\settings\actions\SettingsAction::class,
                'modelClass' => \backend\modules\conf\settings\EmailSettings::class,
                'sectionName' => \backend\modules\conf\settings\EmailSettings::SECTION_EMAIL,
                'view' => 'settings',
            ],
        ];
    }


    public function actionIndex()
    {
        $searchModel = EmailTemplate::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new EmailTemplate();
        return $model->simpleSave('create');
    }

    public function actionUpdate($id)
    {
        $model = EmailTemplate::loadModel($id);
        return $model->simpleSave('update', 'update');
    }

    public function actionDelete($id)
    {
        EmailTemplate::softDelete($id);
    }
}
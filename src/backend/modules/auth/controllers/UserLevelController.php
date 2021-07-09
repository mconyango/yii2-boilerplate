<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\models\UserLevels;
use backend\modules\auth\Session;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * LevelController implements the CRUD actions for UserLevels model.
 */
class UserLevelController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!Yii::$app->user->isGuest && !Session::isDev()) {
            throw new ForbiddenHttpException();
        }
        parent::init();
        $this->enableDefaultAcl = false;
        $this->resourceLabel = 'Account Type';
    }


    public function actionIndex()
    {
        $searchModel = UserLevels::searchModel([
            'defaultOrder' => ['id' => SORT_ASC]
        ]);
        $searchModel->is_active = 1;
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionCreate()
    {
        $model = new UserLevels();
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionUpdate($id)
    {
        $model = UserLevels::loadModel($id);
        return $model->simpleAjaxSaveRenderAjax();
    }


    public function actionDelete($id)
    {
        UserLevels::loadModel($id)->delete();
    }
}

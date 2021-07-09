<?php

namespace backend\modules\auth\controllers;


use backend\modules\auth\Constants;
use backend\modules\auth\models\Resources;
use backend\modules\auth\Session;
use Yii;
use yii\web\ForbiddenHttpException;


class ResourceController extends Controller
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
        $this->resourceLabel = 'Resource';
        $this->activeSubMenu = Constants::SUBMENU_RESOURCES;
    }

    public function actionIndex()
    {
        $searchModel = Resources::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
        ]);

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new Resources();
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionUpdate($id)
    {
        $model = Resources::loadModel($id);
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionDelete($id)
    {
        Resources::softDelete($id);
    }
}
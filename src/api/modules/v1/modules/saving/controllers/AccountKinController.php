<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use api\modules\v1\models\Client;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\saving\models\AccountKin;
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class AccountKinController extends Controller
{
    public function init()
    {
        $this->modelClass = AccountKin::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex($account_id, $client_id = null,  $kin_id = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $searchModel = AccountKin::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'with' => ['kin', 'account'],
        ]);
        $searchModel->account_id = $account_id;
        $searchModel->is_active = 1;
        $searchModel->kin_id = $kin_id;
        $searchModel->client_id = $client_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return AccountKin::loadModel($id);
    }

    public function actionCreate($account_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new AccountKin(['account_id' => $account_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdate($account_id, $id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = AccountKin::loadModel(['account_id' => $account_id, 'id' => $id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }


}
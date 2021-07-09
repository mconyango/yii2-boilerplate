<?php


namespace api\modules\v1\modules\saving\controllers;


use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\core\models\Client;
use backend\modules\saving\Constants;
use backend\modules\saving\models\WithdrawNotice;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class WithdrawNoticeController extends Controller
{
    public function init()
    {
        $this->modelClass = WithdrawNotice::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($client_id = null, $account_id = null, $org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }

        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['org_id' => $org_id];
        $params = [];
        $searchModel = WithdrawNotice::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['client', 'account'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->account_id = $account_id;
        $searchModel->org_id = $org_id;
        $searchModel->is_active = 1;
        $searchModel->is_withdrawn = 0;
        $searchModel->client_id = $client_id;

        return $searchModel->search();
    }

    public function actionCreate($client_id, $org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }

        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $clientModel = Client::findOne(['id' => $client_id]);
        $client_org_id = $clientModel->org_id;

        if ($org_id !== $client_org_id) {
            throw new BadRequestHttpException('The client does not belong to your organization');
        }

        $model = new WithdrawNotice([
            'client_id' => $client_id,
            'is_active' => 1
        ]);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to Create the Object for unknown reasons');
        }

        return $model;
    }

    public function actionUpdate($id, $client_id = null, $org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $model = WithdrawNotice::loadModel(['id' => $id, 'org_id' => $org_id]);

        if ($model->is_withdrawn) {
            throw new ForbiddenHttpException();
        }

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
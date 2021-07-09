<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use api\modules\v1\models\Client;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\saving\models\Account;
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class AccountController extends Controller
{
    public function init()
    {
        $this->modelClass = Account::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex($org_id = null, $client_id = null, $account_no = null, $account_name = null, $product_id = null,
                                $is_dormant = null, $is_suspended = null, $is_activated = null, $is_closed = null, $branch_id = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $searchModel = Account::searchModel([
            'defaultOrder' => ['account_no' => SORT_ASC],
            'with' => ['client', 'product'],
            'condition' => ['org_id' => $org_id],
        ]);
        $searchModel->client_id = $client_id;
        $searchModel->account_no = $account_no;
        $searchModel->account_name = $account_name;
        $searchModel->product_id = $product_id;
        $searchModel->is_dormant = $is_dormant;
        $searchModel->is_suspended = $is_suspended;
        $searchModel->is_activated = $is_activated;
        $searchModel->is_closed = $is_closed;
        $searchModel->branch_id = $branch_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Account::loadModel($id);
    }

    public function actionCreate($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $clientModel = Client::loadModel($client_id);
        $model = new Account([
            'client_id' => $client_id,
            'date_opened' => DateUtils::getToday(),
            'org_id' => $clientModel->org_id,
            'is_activated' => 1,
        ]);
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

    public function actionUpdate($client_id, $account_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = Account::loadModel(['client_id' => $client_id, 'id' => $account_id]);
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
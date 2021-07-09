<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\loan\models\Collateral;
use backend\modules\loan\models\LoanAccount;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class LoanCollateralController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanAccount::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;
        parent::init();
    }

    public function actionIndex($loan_id)
    {
        $loanModel = LoanAccount::loadModel($loan_id);
        $condition = '';
        $params = [];
        $searchModel = Collateral::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->loan_id = $loan_id;
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Collateral::loadModel($id);
    }

    public function actionCreate($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $loanModel = LoanAccount::loadModel($loan_id);
        $model = new Collateral([
            'loan_id' => $loan_id,
            'currency' => $loanModel->default_currency,
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

    public function actionUpdate($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $model = Collateral::loadModel($id);
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
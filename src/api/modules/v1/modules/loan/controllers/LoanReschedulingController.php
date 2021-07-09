<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\loan\models\LoanAccount;
use backend\modules\loan\models\LoanRefinancing;
use backend\modules\loan\models\LoanRescheduling;
use common\widgets\lineItem\LineItem;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class LoanReschedulingController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanRescheduling::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex($loan_id)
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $condition = '';
        $params = [];
        list($condition, $params) = LoanRescheduling::appendOrgSessionIdCondition($condition, $params);
        $searchModel = LoanRescheduling::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->loan_id = $loan_id;
        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return LoanRescheduling::loadModel($id);
    }

    public function actionCreate($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new LoanRescheduling([
            'loan_id' => $loan_id,
        ]);
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::debug($e->getTrace());
                throw new HttpException(500, $e->getTraceAsString());
            }
        }

        return $model;
    }

    public function actionGetSchedule($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $loanModel = LoanAccount::loadModel($loan_id);

        $model = new LoanRescheduling([
            'loan_id' => $loanModel->id,
        ]);

        $model->load(Yii::$app->request->post(), '');
        if ($model->validate(['payment_cycle', 'repayment_period'])) {
            $model->setAmortization();
            return $model->getRepaymentScheduleModels();
        }
        else {
            return $model->getErrors();
        }
    }

    public function actionTransfer($loan_id)
    {
        //Transfer Principal Balance;
        $loanModel = LoanAccount::loadModel($loan_id);
        $model = new LoanRescheduling([
            'loan_id' => $loanModel->id,
        ]);
        $model->amount = $loanModel->loan_balance;
        $refinancingModelClassName = LoanRefinancing::class;
        if ($resp = LineItem::finishAction($model, $refinancingModelClassName, 'disbursement_id', true, [
            'redirectRoute' => 'voucher',
            'idParam' => 'id',
        ])) {
            return json_decode($resp);
        }
    }



}
<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\core\models\ClientWorkInformation;
use backend\modules\loan\models\LoanAccount;
use backend\modules\loan\models\LoanAppraisal;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class LoanAppraisalController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanAppraisal::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex()
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);;
        return [];
    }
    public function actionCreate($loan_id, $work_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $loanModel = LoanAccount::loadModel($loan_id);
        $workModel = ClientWorkInformation::loadModel($work_id);
        $model = new LoanAppraisal([
            'loan_id' => $loan_id,
            'work_id' => $workModel->id,
        ]);
        $loanModel->setDefaultAmortization();
        $model->loan_installment = $loanModel->amortization->getFirstInstallmentAmount();
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

    public function actionGetCalculatedFields($loan_id, $work_id)
    {
        $model = new LoanAppraisal([
            'loan_id' => $loan_id,
            'work_id' => $work_id,
        ]);

        $model->load(Yii::$app->request->post(), '');
        $model->setCalculatedValues();
        return $model;
    }


}
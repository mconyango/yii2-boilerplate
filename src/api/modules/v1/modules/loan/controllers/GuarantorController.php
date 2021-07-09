<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\loan\models\Guarantor;
use backend\modules\loan\models\LoanAccount;
use common\helpers\DateUtils;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class GuarantorController extends Controller
{
    public function init()
    {
        $this->modelClass = Guarantor::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex()
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);;
        return [];
    }

    protected function saveModel($model){
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::debug($e->getTrace());
                throw new HttpException(500, $e->getTraceAsString());
            }
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionCreateInternal($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $loanModel = LoanAccount::loadModel($loan_id);

        $model = new Guarantor([
            'loan_id' => $loanModel->id,
            'is_member' => 1,
            'is_active' => 1,
            'date_guaranteed' => DateUtils::getToday(),
            'scenario' => Guarantor::SCENARIO_MEMBER,
        ]);

        if (null !== $model->guarantorAccount) {
            $model->saving_account_balance_before = (float)$model->guarantorAccount->available_balance;
        }

        return $this->saveModel($model);

    }

    public function actionCreateExternal($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $loanModel = LoanAccount::loadModel($loan_id);

        $model = new Guarantor([
            'loan_id' => $loanModel->id,
            'is_member' => 0,
            'is_active' => 1,
            'date_guaranteed' => DateUtils::getToday(),
            'scenario' => Guarantor::SCENARIO_NON_MEMBER,
        ]);

        return $this->saveModel($model);
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        return Guarantor::loadModel(['id' => $id, 'is_active' => 1]);
    }

    public function actionUpdate($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = Guarantor::loadModel($id);

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
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionRemove($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = Guarantor::loadModel($id);
        $model->setScenario(Guarantor::SCENARIO_REMOVE);
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
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }


}
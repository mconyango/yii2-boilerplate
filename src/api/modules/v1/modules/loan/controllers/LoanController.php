<?php

namespace api\modules\v1\modules\loan\controllers;


use api\controllers\Controller;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\core\models\Client;
use backend\modules\core\TransactionConstants;
use backend\modules\loan\models\Guarantor;
use backend\modules\loan\models\LoanAccount;
use backend\modules\payment\models\DepositHeader;
use backend\modules\payment\models\DepositTransaction;
use backend\modules\payment\models\ReversalTransaction;
use common\helpers\DateUtils;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class LoanController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanAccount::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex($client_id = null, $product_id = null, $account_no = null, $external_ref = null, $is_fully_secured = null, $is_appraised = null,$from = null, $to = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        //$this->hasPrivilege(Acl::ACTION_VIEW);

        $date_filter = DateUtils::getDateFilterParams($from, $to, 'application_date', false, false);
        $condition = $date_filter['condition'];
        $params = [];
        list($condition, $params) = LoanAccount::appendOrgSessionIdCondition($condition, $params);
        $searchModel = LoanAccount::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['client', 'product', 'loanPurpose'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->client_id = $client_id;
        $searchModel->product_id = $product_id;
        $searchModel->account_no = $account_no;
        $searchModel->external_ref = $external_ref;
        $searchModel->is_appraised = $is_appraised;
        $searchModel->_dateFilterFrom = $date_filter['from'];
        $searchModel->_dateFilterTo = $date_filter['to'];
        $searchModel->is_fully_secured = $is_fully_secured;
        $searchModel->is_cancelled = 0;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return LoanAccount::loadModel($id);
    }

    public function actionCreate($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $model = new LoanAccount([
            'client_id' => $client_id,
        ]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        // we don't need to set this here, it's been set automatically in model if it's null
        $model->loanSettingsModel->load(\Yii::$app->request->post('loanSettings'), '');

        if ($model->validate() && $model->loanSettingsModel->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save();
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::debug($e->getTrace());
                throw new HttpException(500, $e->getTraceAsString());
            }

        } elseif (!$model->hasErrors() && !$model->loanSettingsModel->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        if ($model->loanSettingsModel->hasErrors()) {
            $model->addErrors($model->loanSettingsModel->getErrors());
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = LoanAccount::loadModel($id);

        if (!$model->canBeUpdated()) {
            throw new ForbiddenHttpException();
        }
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $model->loanSettingsModel->load(\Yii::$app->request->post('loanSettings'), '');
        if ($model->validate() && $model->loanSettingsModel->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save();
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::debug($e->getTrace());
                throw new HttpException(500, $e->getTraceAsString());
            }
        } elseif (!$model->hasErrors() && !$model->loanSettingsModel->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        if ($model->loanSettingsModel->hasErrors()) {
            $model->addErrors($model->loanSettingsModel->getErrors());
        }

        return $model;
    }

    public function actionCancel($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = LoanAccount::loadModel($id);
        $model->date_cancelled = DateUtils::getToday();
        $model->setScenario(LoanAccount::SCENARIO_CANCEL);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save();
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

    public function actionApprove($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = LoanAccount::loadModel($id);
        $model->approval_date = DateUtils::getToday();
        $model->setScenario(LoanAccount::SCENARIO_APPROVE);
        $model->setSecuritySummary();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save();
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

    public function actionRecover($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $loan = LoanAccount::loadModel($id);

        if (Yii::$app->request->post()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $loan->recoverFromGuarantors();
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
                return [
                    'loan_id' => $loan->id,
                    'success' => true,
                    'message' => 'Loan successfully recovered from Guarantors'
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage());
                Yii::debug($e->getTrace());
                throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
            }
        }
    }

    public function actionTransferToGuarantor($guarantor_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $guarantor = Guarantor::loadModel(['id' => $guarantor_id]);
        $loan = LoanAccount::loadModel(['id' => $guarantor->loan_id]);
        //$loan->setScenario(LoanAccount::SCENARIO_TRANSFER_TO_GUARANTOR);

        if (Yii::$app->request->post()) {
            if ($loan->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $loan->transferToGuarantor($guarantor);
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(200);
                    return [
                        'loan_id' => $loan->id,
                        'success' => true,
                        'message' => 'Loan successfully transferred to Guarantor'
                    ];
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::debug($e->getTrace());
                    throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
                }
            } else {
                return ['success' => false, 'message' => $loan->getErrors()];
            }
        }

    }

    public function actionGetRepaymentSchedule($client_id, $loan_id = null, $scenario = 'default'){
        $clientModel = Client::loadModel($client_id);
        if (!empty($loan_id)) {
            $model = LoanAccount::loadModel($loan_id);
            $model->setScenario($scenario);
        } else {
            $model = new LoanAccount([
                'client_id' => $clientModel->id,
            ]);
        }
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate(['amount_applied', 'amount_approved', 'application_date', 'approval_date', 'payment_cycle', 'repayment_period'])) {
            $model->setDefaultAmortization();
            return $model->getRepaymentScheduleModels();
        }
        else {
            return $model->getErrors();
        }
    }

    public function actionPreviewSchedule($loan_id){
        $model = LoanAccount::loadModel($loan_id);
        if (!$model->hasSavedRepaymentSchedule()) {
            $model->setDefaultAmortization();
        }

        return $this->renderPartial('@backend/modules/loan/views/loan/partials/_repaymentSchedule', [
            'model' => $model,
        ]);
    }

    public function actionWriteOff($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = LoanAccount::loadModel($id);
        $model->write_off_date = DateUtils::getToday();
        $model->write_off_amount = $model->loan_balance;
        $model->setScenario(LoanAccount::SCENARIO_WRITE_OFF);
        $model->load(Yii::$app->request->post(), '');

        if ($model->validate()) {
            $is_loan = 1;
            $loan = $model;

            $deposit = new DepositTransaction([
                'account_id' => null,
                'loan_account_id' => $id,
                'default_currency' => $loan->default_currency,
                'currency' => $loan->default_currency,
                'entry_type' => TransactionConstants::ENTRY_TYPE_CREDIT,
                'running_balance' => $loan->loan_balance,
                'transaction_date' => DateUtils::getToday(),
                'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
                'is_fixed_deposit' => 0,
                'is_loan' => $is_loan,
                //'original_amount' => $loan->loan_balance,
                'payment_mode_id' => null,
            ]);

            $deposit->client_id = $loan->client_id;
            $deposit->original_amount = $loan->write_off_amount;
            $deposit->ref_no = $loan->write_off_ref;
            $deposit->batch_no = '';
            $deposit->notes = $loan->write_off_notes;
            $deposit->setScenario(TransactionConstants::SCENARIO_WRITE_OFF);

            if ($deposit->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $deposit->save(false);
                    $model->save(false);
                    $transaction->commit();
                    $response = \Yii::$app->getResponse();
                    $response->setStatusCode(200);
                    return ['success' => true, 'message' => Lang::t('Loan Written off successfully')];

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::debug($e->getTrace());
                    throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
                }
            } else {
                return ['success' => false, 'message' => $deposit->getErrors()];
            }
        }
        else {
            return ['success' => false, 'message' => $model->getErrors()];
        }

    }

    public function actionReverseWriteOff($id)
    {
        $loan = LoanAccount::loadModel($id);
        $txn_id = $loan->write_off_txn;
        $transaction_type = \backend\modules\core\TransactionConstants::TRANSACTION_TYPE_DEPOSIT;

        $model = new ReversalTransaction([
            'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
            'transaction_type' => $transaction_type,
            'is_active' => 1,
        ]);

        $txnModel = DepositHeader::loadModel($txn_id);
        $model->org_id = $txnModel->org_id;
        $model->client_id = $txnModel->client_id;
        $model->deposit_txn_id = $txn_id;

        $model->load(Yii::$app->request->post(), '');

        if ($model->validate() && $model->validateTransactions()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
                return [
                    'success' => true,
                    'message' => Lang::t('Loan Write-off reversed successfully'),
                    //'loan' => $loan,
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getTraceAsString());
                throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
            }
        } else {
            return ['success' => false, 'message' => $model->getErrors()];
        }

    }

}
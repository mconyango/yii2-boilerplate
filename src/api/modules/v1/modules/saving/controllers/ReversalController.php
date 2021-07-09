<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use backend\modules\accounting\models\TransactionCode;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\core\TransactionConstants;
use backend\modules\loan\models\LoanAccount;
use backend\modules\payment\models\DepositHeader;
use backend\modules\payment\models\DepositTransaction;
use backend\modules\payment\models\ReversalTransaction;
use backend\modules\saving\models\Account;
use backend\modules\saving\models\InternalTransfer;
use backend\modules\saving\models\Transaction;
use backend\modules\saving\models\WithdrawalTransaction;
use common\helpers\DateUtils;
use common\helpers\Lang;
use common\helpers\Url;
use kartik\mpdf\Pdf;
use Yii;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class ReversalController extends Controller
{
    public function init()
    {
        $this->modelClass = ReversalTransaction::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex()
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        return [];
    }

    public function actionCreate($transaction_type, $transaction_id)
    {
        $txn_id = $transaction_id;
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ReversalTransaction([
            'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
            'transaction_type' => $transaction_type,
            'is_active' => 1,
        ]);
        switch ($transaction_type) {
            case TransactionConstants::TRANSACTION_TYPE_DEPOSIT:
                $txnModel = DepositHeader::loadModel($txn_id);
                $model->org_id = $txnModel->org_id;
                $model->client_id = $txnModel->client_id;
                $model->deposit_txn_id = $txn_id;
                $returnRoute = '/payment/deposit/view';
                break;
            case TransactionConstants::TRANSACTION_TYPE_WITHDRAWAL:
                $txnModel = WithdrawalTransaction::loadModel($txn_id);
                $model->org_id = $txnModel->org_id;
                $model->client_id = $txnModel->client_id;
                $model->withdrawal_txn_id = $txn_id;
                $returnRoute = '/saving/withdrawal/view';
                break;
            case TransactionConstants::TRANSACTION_TYPE_TRANSFER:
                $txnModel = InternalTransfer::loadModel($txn_id);
                $model->org_id = $txnModel->org_id;
                $model->client_id = $txnModel->from_client_id;
                $model->transfer_txn_id = $txn_id;
                $returnRoute = '/saving/transfer/view';
                break;
            default:
                throw new BadRequestHttpException();
        }
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->validateTransactions()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::error($e->getTraceAsString());
                throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
            }
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;

    }

}
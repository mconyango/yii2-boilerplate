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
use backend\modules\saving\models\Account;
use backend\modules\saving\models\Transaction;
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

class DepositController extends Controller
{
    public function init()
    {
        $this->modelClass = Transaction::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex($client_id = null, $ref_no = null, $batch_no = null, $payment_mode_id = null, $transaction_status = null, $from = null, $to = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $date_filter = DateUtils::getDateFilterParams($from, $to, 'transaction_date', false, false);
        $condition = $date_filter['condition'];
        $params = [];
        list($condition, $params) = DepositHeader::appendOrgSessionIdCondition($condition, $params);

        $searchModel = DepositHeader::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->client_id = $client_id;
        $searchModel->payment_mode_id = $payment_mode_id;
        $searchModel->ref_no = $ref_no;
        $searchModel->batch_no = $batch_no;
        $searchModel->transaction_status = $transaction_status;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return DepositHeader::loadModel($id);
    }

    public function actionCreate($account_id, $is_loan = 0)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        if ($is_loan) {
            $accountModel = LoanAccount::loadModel($account_id);
        } else {
            $accountModel = Account::loadModel($account_id);
        }
        // generate a new Transaction Code
        $tx_code = TransactionCode::generateTransactionCode();

        $model = new DepositTransaction([
            'account_id' => !$is_loan ? $account_id : null,
            'loan_account_id' => $is_loan ? $account_id : null,
            'default_currency' => $accountModel->default_currency,
            'currency' => $accountModel->default_currency,
            'entry_type' => TransactionConstants::ENTRY_TYPE_CREDIT,
            'running_balance' => $is_loan ? $accountModel->loan_balance : $accountModel->current_balance,
            'transaction_date' => DateUtils::getToday(),
            'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
            'is_fixed_deposit' => !$is_loan ? $accountModel->is_fixed_account : 0,
            'is_loan' => $is_loan,
            'original_amount' => $is_loan ? $accountModel->loan_balance : null,
            'transaction_code' => $tx_code,
        ]);
        $model->client_id = $is_loan ? $model->loanAccount->client_id : $model->account->client_id;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();

                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw new HttpException(500, $e->getMessage());
            }
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;

    }

    public function actionDownloadReceipt($id, $paperSize = null)
    {
        if (empty($paperSize)) {
            $paperSize = 'A6';
        }
        $model = DepositHeader::loadModel($id);
        if (!$model->isCompleted()) {
            throw new ForbiddenHttpException();
        }

        $file_name = 'deposit-slip-' . $model->receipt_no;
        $title = 'Receipt #' . $model->receipt_no;
        $pdfHeader = [
            'L' => [
                //'content' => $model->org->name,
                'content' => '',
                'font-size' => 8,
                'color' => '#333333',
            ],
            'C' => [
                //'content' => $title,
                'content' => '',
                'font-size' => 16,
                'color' => '#333333',
            ],
            'R' => [
                //'content' => Lang::t('Generated') . ': ' . DateUtils::formatToLocalDate(date(time()), "D, d-M-Y g:i a"),
                'content' => '',
                'font-size' => 8,
                'color' => '#333333',
            ],
        ];

        $config = [
            'mode' => 'UTF-8',
            'format' => 'A4-L',
            'destination' => 'D',
            'marginTop' => 15,
            'marginBottom' => 0,
            'cssInline' =>
                '.table {margin-bottom:5px;}' .
                'p,th,td{font-size:11px!important;line-height: normal!important;}' .
                'th,td {border:none!important;padding: 2px!important;line-height: 1!important;}' .
                'hr {margin-top:2px;margin-bottom:2px;}' .
                'h1,h3 {line-height:normal;margin:0;}' .
                'h1 {font-size: 18px;}' .
                'h3 {font-size: 14px;}' .
                'p {margin-bottom:5px;}' .
                'img {height:80px!important;}',
            'methods' => [
                'SetHeader' => [
                    ['odd' => $pdfHeader, 'even' => $pdfHeader],
                ],
                'SetFooter' => false,
            ],
            'options' => [
                'title' => $title,
            ],
        ];

        $html = $this->renderPartial('@backend/modules/payment/views/deposit/_receipt', ['model' => $model]);

        $config['filename'] = "{$file_name}.pdf";
        $config['methods']['SetAuthor'] = [$model->org->name];
        $config['methods']['SetCreator'] = [Session::userName()];
        $config['content'] = $html;
        //$config['format'] = [100, 300];
        $config['format'] = $paperSize;
        $config['destination'] = Pdf::DEST_BROWSER;
        $pdf = new Pdf($config);
        //Yii::$app->response->data = $pdf->render();
        return $pdf->render();
    }

}
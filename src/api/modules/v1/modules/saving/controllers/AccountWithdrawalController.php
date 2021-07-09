<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\saving\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\accounting\models\TransactionCode;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\models\Client;
use backend\modules\core\TransactionConstants;
use backend\modules\saving\models\Account;
use backend\modules\saving\models\WithdrawalTransaction;
use common\helpers\DateUtils;
use common\helpers\Lang;
use kartik\mpdf\Pdf;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class AccountWithdrawalController extends Controller
{
    public function init()
    {
        $this->modelClass = WithdrawalTransaction::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($client_id = null, $account_id = null, $org_id = null, $ref_no = null)
    {
        $clientModel = null;
        if (!empty($client_id)) {
            $clientModel = Client::loadModel($client_id);
        }
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['org_id' => $org_id];
        $searchModel = WithdrawalTransaction::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'with' => ['client', 'account'],
            'condition' => $condition,
        ]);
        $searchModel->client_id = $client_id;
        $searchModel->account_id = $account_id;
        $searchModel->ref_no = $ref_no;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return WithdrawalTransaction::loadModel($id);
    }

    public function actionCreate($account_id, $withdraw_notice_id = null)
    {
        $accountModel = Account::loadModel($account_id);

        $model = new WithdrawalTransaction([
            'account_id' => $account_id,
            'default_currency' => $accountModel->default_currency,
            'currency' => $accountModel->default_currency,
            'entry_type' => TransactionConstants::ENTRY_TYPE_DEBIT,
            'running_balance' => $accountModel->current_balance,
            'transaction_date' => DateUtils::getToday(),
            'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
            'withdraw_notice_id' => $withdraw_notice_id,
            'transaction_code' => TransactionCode::generateTransactionCode(),
        ]);
        if ($model->withdrawNotice !== null) {
            $model->original_amount = $model->withdrawNotice->amount;
            $model->currency = $model->withdrawNotice->currency;
            $model->ref_no = $model->withdrawNotice->ref_no;
            $model->notes = $model->withdrawNotice->notes;
            $model->payment_mode_id = $model->withdrawNotice->payment_mode_id;
        }
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
            if ($model->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $model->save(false);
                    $transaction->commit();

                    return $model;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error($e->getMessage());
                    Yii::debug($e->getTrace());
                    throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
                }
            }
            elseif (!$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
            }

        return $model;
    }

    public function actionReceipt($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $model = WithdrawalTransaction::loadModel($id);
        if (!$model->isCompleted()) {
            throw new ForbiddenHttpException();
        }
        return $model;
    }

    public function actionDownloadReceipt($id, $paperSize = null)
    {
        if (empty($paperSize)) {
            $paperSize = 'A6';
        }
        $model = WithdrawalTransaction::loadModel($id);
        if (!$model->isCompleted()) {
            throw new ForbiddenHttpException();
        }

        $file_name = 'withdrawal-slip-' . $model->receipt_no;
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

        $html = $this->renderPartial('@backend/modules/saving/views/withdrawal/_receipt', ['model' => $model]);

        $config['filename'] = "{$file_name}.pdf";
        $config['methods']['SetAuthor'] = [$model->org->name];
        $config['methods']['SetCreator'] = [Session::userName()];
        $config['content'] = $html;
        // $config['format'] = [100, 300];
        $config['format'] = $paperSize;
        $config['destination'] = Pdf::DEST_BROWSER;
        $pdf = new Pdf($config);
        //Yii::$app->response->data = $pdf->render();
        return $pdf->render();
    }
}
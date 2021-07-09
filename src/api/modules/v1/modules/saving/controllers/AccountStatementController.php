<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use api\modules\v1\models\Client;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\loan\models\LoanAccount;
use backend\modules\saving\models\Account;
use backend\modules\saving\models\Transaction;
use common\helpers\DateUtils;
use kartik\mpdf\Pdf;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class AccountStatementController extends Controller
{
    public function init()
    {
        $this->modelClass = Account::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex($client_id, $account_id = null, $loan_account_id = null, $from = null, $to = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $clientModel = \backend\modules\core\models\Client::loadModel($client_id);
        $date_filter = DateUtils::getDateFilterParams($from, $to, 'transaction_date', true, false);
        $condition = $date_filter['condition'];
        $params = [];

        //savings
        list($condition, $params) = Transaction::appendOrgSessionIdCondition($condition, $params);

        $savingSearchModel = Transaction::searchModel([
            'defaultOrder' => ['transaction_date' => SORT_ASC],
            'with' => ['client', 'account', 'paymentMode'],
            'condition' => $condition,
            'params' => $params,
            'enablePagination' => false,
        ]);
        $savingSearchModel->client_id = $client_id;
        $savingSearchModel->account_id = $account_id;
        $accountsCondition = ['client_id' => $client_id];
        if (!empty($account_id)) {
            $accountsCondition['id'] = $account_id;
        }
        $savingAccountModels = Account::find()->andWhere($accountsCondition)->all();

        //loans
        $loanSearchModel = \backend\modules\loan\models\Transaction::searchModel([
            'defaultOrder' => ['transaction_date' => SORT_ASC],
            'with' => ['client', 'account', 'paymentMode'],
            'condition' => $condition,
            'params' => $params,
            'enablePagination' => false,
        ]);
        $loanSearchModel->client_id = $client_id;
        $loanSearchModel->account_id = $loan_account_id;
        $loanAccountsCondition = ['client_id' => $client_id, 'is_disbursed' => 1];
        if (!empty($loan_account_id)) {
            $loanAccountsCondition['id'] = $loan_account_id;
        }
        $loanAccountModels = LoanAccount::find()->andWhere($loanAccountsCondition)->all();

        $filterOptions = [
            'from' => $date_filter['from'],
            'to' => $date_filter['to'],
        ];
        // download document
        $html = $this->renderPartial('@backend/modules/saving/views/account-statement/_statement', [
            'clientModel' => $clientModel,
            'savingSearchModel' => $savingSearchModel,
            'savingAccountModels' => $savingAccountModels,
            'loanSearchModel' => $loanSearchModel,
            'loanAccountModels' => $loanAccountModels,
            'filterOptions' => $filterOptions,
        ]);
        return $this->generatePDF($clientModel, $html);
    }

    protected function generatePDF(\backend\modules\core\models\Client $clientModel, $html)
    {
        $file_name = 'account-statements-' . $clientModel->code;
        $title = 'Account Statement';
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

        $config['filename'] = "{$file_name}.pdf";
        $config['methods']['SetAuthor'] = [$clientModel->org->name];
        $config['methods']['SetCreator'] = [Session::userName()];
        $config['content'] = $html;
        $config['format'] = 'A4';
        $config['destination'] = Pdf::DEST_BROWSER;
        $pdf = new Pdf($config);
        //Yii::$app->response->data = $pdf->render();
        return $pdf->render();
    }


}
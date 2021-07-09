<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\core\models\Client;
use backend\modules\loan\models\ExternalRefinancing;
use backend\modules\loan\models\LoanAccount;
use backend\modules\loan\models\LoanAccountFees;
use backend\modules\loan\models\LoanDisbursement;
use backend\modules\loan\models\LoanRefinancing;
use common\helpers\DateUtils;
use common\models\Model;
use common\widgets\lineItem\LineItem;
use kartik\mpdf\Pdf;
use Yii;
use yii\helpers\Json;
use yii\rest\Serializer;
use yii\web\BadRequestHttpException;

class DisbursementController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanDisbursement::class;
        $this->resource = \backend\modules\loan\Constants::RES_PRODUCT_LOAN;;
        parent::init();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return LoanDisbursement::loadModel($id);
    }

    public function actionIndex($client_id = null, $loan_id= null, $payment_mode_id = null, $ref_no = null)
    {

        $condition = '';
        $params = [];

        $searchModel = LoanDisbursement::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'with' => ['loan'],
            'condition' => $condition,
            'params' => $params,
        ]);

        $searchModel->client_id = $client_id;
        $searchModel->loan_id = $loan_id;
        $searchModel->payment_mode_id = $payment_mode_id;
        $searchModel->ref_no = $ref_no;

        return $searchModel->search();
    }


    public function actionCreate($loan_id, $option = null)
    {
        $loanModel = LoanAccount::loadModel($loan_id);
        $model = new LoanDisbursement([
            'loan_id' => $loanModel->id,
            'has_external_refinancing' => $option == 2 ? 1 : 0,
        ]);
        if (null !== $option) {
            $model->refinance_other_loans = 1;
        }
        // $model->amount=$loanModel->amount_applied;
        $refinancingModelClassName = $option == 2 ? ExternalRefinancing::class : LoanRefinancing::class;
        if ($resp = LineItem::finishAction($model, $refinancingModelClassName, 'disbursement_id', true, [
            'redirectRoute' => 'voucher',
            'idParam' => 'id',
        ])) {
            //
            //$model->updateRefinancedLoans();
            // since the response from LineItem is json encoded, we need to decode it back to array to avoid double encoding by the API response formatter
            return json_decode($resp);
        }
    }

    public function actionGetCalculatedFields($loan_id, $option = 1)
    {
        $model = new LoanDisbursement([
            'loan_id' => $loan_id,
        ]);
        $model->load(Yii::$app->request->post());
        /* @var $refinancingModel LoanRefinancing|ExternalRefinancing */
        $refinancingModels = [];
        $totalRefinancingFees = 0;
        $models = Model::createMultiple($option == 2 ? ExternalRefinancing::class : LoanRefinancing::class);
        foreach ($models as $i => $refinancingModel) {
            $fees = LoanAccountFees::getTotalCalculatedRefinancingFees($model->loan, $refinancingModel->amount);
            $refinancingModel->total_fees = $fees;
            $refinancingModels[$i] = $refinancingModel;
            $totalRefinancingFees += $fees;
        }
        $model->refinancing_fees = $totalRefinancingFees;
        $model->setAmortization();
        $disbursementData = Yii::createObject(Serializer::class)->serialize($model);
        $refinancingData = Yii::createObject(Serializer::class)->serialize($refinancingModels);
        $data = Yii::createObject(Serializer::class)->serialize(['disbursementData' => $model, 'refinancingData' => $refinancingModels]);
        return $data;

    }

    public function actionVoucher($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = LoanDisbursement::loadModel($id);
        return $model;
    }

    public function actionDownloadVoucher($id, $paperSize = null)
    {
        if (empty($paperSize)) {
            $paperSize = 'A6';
        }
        $model = LoanDisbursement::loadModel($id);

        $file_name = 'loan-disbursement-voucher-' . $model->receipt_no;
        $title = 'Voucher #' . $model->receipt_no;
        $pdfHeader = [
            'L' => [
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

        $html = $this->renderPartial('@backend/modules/loan/views/loan-disbursement/_voucher', ['model' => $model]);

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

    public function actionGetRefinancingFees($loan_id, $amount)
    {
        $loan = LoanAccount::loadModel($loan_id);
        $fees = LoanAccountFees::getTotalCalculatedRefinancingFees($loan, $amount);
        return $fees;
    }

}
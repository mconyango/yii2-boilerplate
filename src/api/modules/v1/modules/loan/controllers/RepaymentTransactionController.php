<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\core\TransactionConstants;
use backend\modules\loan\models\Transaction;
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;

class RepaymentTransactionController extends Controller
{
    public function init()
    {
        $this->modelClass = Transaction::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;
        parent::init();
    }

    public function actionIndex($client_id = null, $product_id = null, $ref_no = null, $batch_no = null, $payment_mode_id = null, $currency = null, $from = null, $to = null)
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
        list($condition, $params) = Transaction::appendOrgSessionIdCondition($condition, $params);

        $searchModel = Transaction::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->client_id = $client_id;
        $searchModel->payment_mode_id = $payment_mode_id;
        $searchModel->ref_no = $ref_no;
        $searchModel->batch_no = $batch_no;
        $searchModel->entry_type = TransactionConstants::ENTRY_TYPE_CREDIT;
        $searchModel->product_id = $product_id;
        $searchModel->currency = $currency;
        $searchModel->_dateFilterFrom = $date_filter['from'];
        $searchModel->_dateFilterTo = $date_filter['to'];

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Transaction::loadModel($id);
    }

}
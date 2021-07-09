<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\product\models\Product;
use backend\modules\reports\models\SavingTransaction;
use backend\modules\saving\Constants;
use backend\modules\saving\models\Account;
use common\helpers\Lang;
use yii\web\BadRequestHttpException;

class DashboardController extends Controller
{
    public function init()
    {
        $this->modelClass = Account::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($currency, $transaction_type, $dateRange = null, $payment_mode_id = null, $product_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $product = !empty($product_id) ? Product::loadModel($product_id) : null;
        $filters = [
            'transaction_type' => $transaction_type,
            'currency' => $currency,
            'payment_mode_id' => $payment_mode_id,
            'product_id' => $product_id,
        ];

        return [
            'title' => Lang::t('{product} stats', ['product' => $product !== null ? $product->name : 'Savings']),
            'stats' => [
                'Today' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_TODAY, 'amount', $filters)),
                'This week' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_THIS_WEEK, 'amount', $filters)),
                'Last week' =>  number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_LAST_WEEK, 'amount', $filters)),
                'This month' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_THIS_MONTH, 'amount', $filters)),
                'Last month' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_LAST_MONTH, 'amount', $filters)),
                'This year' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_THIS_YEAR, 'amount', $filters)),
                'Last year' => number_format(SavingTransaction::getDashboardStats(SavingTransaction::STATS_LAST_YEAR, 'amount', $filters))
            ],
        ];
    }


}
<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\saving\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\models\Bank;
use backend\modules\saving\Constants;
use backend\modules\saving\models\DividendPayment;
use backend\modules\saving\models\DividendPaymentSchedule;
use yii\web\BadRequestHttpException;

class DividendPaymentController extends Controller
{
    public function init()
    {
        $this->modelClass = DividendPayment::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($payment_schedule_id)
    {
        $model = DividendPaymentSchedule::loadModel($payment_schedule_id);
        $condition = '';
        $params = [];
        $searchModel = DividendPayment::searchModel([
            'defaultOrder' => ['client_id' => SORT_ASC, 'id' => SORT_DESC],
            'with' => ['client', 'account'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->payment_schedule_id = $payment_schedule_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return DividendPayment::loadModel($id);
    }

}
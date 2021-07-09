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
use backend\modules\saving\Constants;
use backend\modules\saving\models\DividendPaymentProrate;
use yii\web\BadRequestHttpException;

class DividendPaymentProrateController extends Controller
{
    public function init()
    {
        $this->modelClass = DividendPaymentProrate::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($payment_id)
    {
        $condition = '';
        $params = [];
        $searchModel = DividendPaymentProrate::searchModel([
            'defaultOrder' => ['month' => SORT_ASC],
            'with' => ['payment'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->payment_id = $payment_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return DividendPaymentProrate::loadModel($id);
    }

}
<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\saving\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\models\PaymentMode;
use yii\web\BadRequestHttpException;

class PaymentModesController extends Controller
{
    public function init()
    {
        $this->modelClass = PaymentMode::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $condition = ['org_id' => $org_id];
        $searchModel = PaymentMode::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->is_active = 1;

        return $searchModel->search();
    }


    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return PaymentMode::loadModel($id);
    }

}
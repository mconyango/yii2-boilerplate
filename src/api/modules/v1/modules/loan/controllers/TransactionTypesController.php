<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\loan\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\loan\models\Transaction;
use yii\web\BadRequestHttpException;

class TransactionTypesController extends Controller
{
    public function init()
    {
        $this->modelClass = Transaction::class;
        $this->resource = \backend\modules\loan\Constants::RES_PRODUCT_LOAN;

        parent::init();
    }

    public function actionIndex()
    {
        $data = [];
        $status = Transaction::transactionTypeOptions();
        foreach ($status as $key => $value) {
            $data[] = [
                'id' => $key,
                'label' => $value,
            ];
        }

        if (Session::isOrganization() || Session::isPrivilegedAdmin()) {
            return $data;
        }
        else{
            throw new BadRequestHttpException();
        }
    }
}
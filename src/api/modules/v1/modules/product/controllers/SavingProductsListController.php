<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\product\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\product\models\Product;
use yii\web\BadRequestHttpException;

class SavingProductsListController extends Controller
{
    public function init()
    {
        $this->modelClass = Product::class;
        $this->resource = \backend\modules\product\Constants::RES_PRODUCT;

        parent::init();
    }

    public function actionIndex()
    {
        $is_loan = 0;
        $condition = '';
        $params = [];
        $searchModel = Product::searchModel([
            'defaultOrder' => ['code' => SORT_ASC],
            'with' => ['org'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->is_active = 1;
        $searchModel->is_loan = $is_loan;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Product::loadModel($id);
    }

}
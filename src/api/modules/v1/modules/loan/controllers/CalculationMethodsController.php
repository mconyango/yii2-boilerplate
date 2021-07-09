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
use backend\modules\core\models\Currency;
use backend\modules\loan\models\CalculationMethod;
use yii\web\BadRequestHttpException;

class CalculationMethodsController extends Controller
{
    public function init()
    {
        $this->modelClass = CalculationMethod::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex()
    {
        $searchModel = CalculationMethod::searchModel([
            'defaultOrder' => ['code' => SORT_ASC],
        ]);
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return CalculationMethod::loadModel($id);
    }

}
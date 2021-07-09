<?php

namespace api\modules\v1\modules\loan\controllers;

use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\loan\models\GuarantorReleaseHistory;
use yii\web\BadRequestHttpException;

class GuarantorReleaseHistoryController extends Controller
{
    public function init()
    {
        $this->modelClass = GuarantorReleaseHistory::class;
        $this->resource = \backend\modules\loan\Constants::RES_PRODUCT_LOAN;;;
        parent::init();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return GuarantorReleaseHistory::loadModel($id);
    }


    public function actionIndex($loan_id)
    {
        $condition = '';
        $params = [];
        if (Session::isOrganization()) {
            list($condition, $params) = GuarantorReleaseHistory::appendOrgSessionIdCondition($condition, $params);
        }
        $searchModel = GuarantorReleaseHistory::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'with' => ['guarantor', 'loan'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->loan_id = $loan_id;

        return $searchModel->search();
    }

}
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
use backend\modules\loan\models\LoanPurposeReference;
use yii\web\BadRequestHttpException;

class PurposeReferenceController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanPurposeReference::class;
        $this->resource = \backend\modules\loan\Constants::RES_LOAN;

        parent::init();
    }

    public function actionIndex($org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }

        $condition = ['org_id' => $org_id];
        $params = [];
        $searchModel = LoanPurposeReference::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return LoanPurposeReference::loadModel($id);
    }

}
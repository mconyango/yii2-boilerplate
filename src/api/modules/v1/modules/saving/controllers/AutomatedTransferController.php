<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\saving\controllers;


use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\saving\models\AutomatedInternalTransfers;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class AutomatedTransferController extends Controller
{
    public function init()
    {
        $this->modelClass = AutomatedInternalTransfers::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($client_id = null, $from_account_id = null, $to_account_id = null)
    {

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['org_id' => $org_id];
        $searchModel = AutomatedInternalTransfers::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->from_account_id = $from_account_id;
        $searchModel->to_account_id = $to_account_id;
        $searchModel->client_id = $client_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return AutomatedInternalTransfers::loadModel($id);
    }

}
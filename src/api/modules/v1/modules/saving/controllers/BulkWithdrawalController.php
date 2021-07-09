<?php

namespace api\modules\v1\modules\saving\controllers;

use api\controllers\Controller;
use api\modules\v1\models\Client;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\core\TransactionConstants;
use backend\modules\saving\models\AccountKin;
use backend\modules\saving\models\BulkWithdrawal;
use common\helpers\DateUtils;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class BulkWithdrawalController extends Controller
{
    public function init()
    {
        $this->modelClass = BulkWithdrawal::class;
        $this->resource = \backend\modules\saving\Constants::RES_PRODUCT_SAVING;
        parent::init();
    }

    public function actionIndex($product_id = null, $payment_mode_id = null, $is_processed = null, $ref_no = null, $from = null, $to = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $date_filter = DateUtils::getDateFilterParams($from, $to, 'transaction_date', false, false);
        $condition = $date_filter['condition'];
        $params = [];
        list($condition, $params) = BulkWithdrawal::appendOrgSessionIdCondition($condition, $params);
        $searchModel = BulkWithdrawal::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['product', 'paymentMode'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->product_id = $product_id;
        $searchModel->payment_mode_id = $payment_mode_id;
        $searchModel->is_processed = $is_processed;
        $searchModel->ref_no = $ref_no;
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return BulkWithdrawal::loadModel($id);
    }

    public function actionCreate($client_id = null, $product_id = null)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $clientModel = null;
        if (!empty($client_id)) {
            $clientModel = Client::loadModel($client_id);
        }

        $org_id = null;
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $model = new BulkWithdrawal([
            'org_id' => $org_id,
            'product_id' => $product_id,
            'is_active' => 1,
            'is_processed' => 0,
        ]);
        if (!empty($client_id)) {
            $model->clients_list = [$client_id];
            $model->clients_list_option = TransactionConstants::LIST_OPTION_USE_LIST;
        }
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();

                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw new HttpException(500, $e->getMessage());
            }

        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }


}
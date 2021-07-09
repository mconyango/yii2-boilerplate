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
use backend\modules\core\TransactionConstants;
use backend\modules\saving\Constants;
use backend\modules\saving\models\DividendPaymentSchedule;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class DividendPaymentScheduleController extends Controller
{
    public function init()
    {
        $this->modelClass = DividendPaymentSchedule::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($product_id = null, $schedule_name = null)
    {
        $condition = '';
        $params = [];
        list($condition, $params) = DividendPaymentSchedule::appendOrgSessionIdCondition($condition, $params);
        $searchModel = DividendPaymentSchedule::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['product', 'depositProduct'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->product_id = $product_id;
        $searchModel->schedule_name = $schedule_name;
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return DividendPaymentSchedule::loadModel($id);
    }

    public function actionCreate($product_id = null, $client_id = null, $group_id = null)
    {
        $org_id = null;
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $model = new DividendPaymentSchedule([
            'org_id' => $org_id,
            'product_id' => $product_id,
            'is_active' => 1,
            'is_processed' => 0,
            'total_allocation_amount_is_percent' => 0,
            'capitalization_amount_is_percent' => 0,
        ]);
        if (!empty($client_id)) {
            $model->clients_list = [$client_id];
            $model->clients_list_option = TransactionConstants::LIST_OPTION_USE_LIST;
        }
        if (!empty($group_id)) {
            $model->client_group_list = [$group_id];
            $model->client_group_list_option = TransactionConstants::LIST_OPTION_USE_LIST;
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
        }

        return $model;
    }

    public function actionPostSchedule($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $model = DividendPaymentSchedule::loadModel($id);
        if (!$model->is_processed) {
            throw new ForbiddenHttpException();
        }

        $model->setScenario(DividendPaymentSchedule::SCENARIO_POST);
        $model->posting_remarks = $model->schedule_name;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->queueForPosting(true)) {
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
            } else {
                throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
            }
        }

        return $model;
    }
}
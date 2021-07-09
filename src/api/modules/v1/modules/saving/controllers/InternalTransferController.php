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
use backend\modules\conf\settings\SystemSettings;
use backend\modules\core\models\Client;
use backend\modules\core\TransactionConstants;
use backend\modules\saving\Constants;
use backend\modules\saving\models\AccountDocument;
use backend\modules\saving\models\InternalTransfer;
use common\helpers\DateUtils;
use common\helpers\FileManager;
use common\helpers\Lang;
use common\widgets\fineuploader\UploadHandler;
use Yii;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class InternalTransferController extends Controller
{
    public function init()
    {
        $this->modelClass = AccountDocument::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($client_id = null, $account_id = null, $ref_no = null, $payment_mode_id = null, $transaction_status = null, $from = null, $to = null)
    {
        $clientModel = null;
        if (!empty($client_id)) {
            $clientModel = Client::loadModel($client_id);
        }
        $date_filter = DateUtils::getDateFilterParams($from, $to, 'transaction_date', false, false);
        $condition = $date_filter['condition'];
        $params = [];
        $searchModel = InternalTransfer::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['fromClient', 'fromAccount', 'toClient', 'toAccount', 'paymentMode'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->from_client_id = $client_id;
        $searchModel->from_account_id = $account_id;
        $searchModel->payment_mode_id = $payment_mode_id;
        $searchModel->ref_no = $ref_no;
        $searchModel->transaction_status = $transaction_status;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        $model = InternalTransfer::loadModel($id);

        return $model;
    }

    public function actionCreate($from_client_id = null, $from_account_id = null, $to_client_id = null, $to_account_id = null)
    {
        $clientModel = null;
        if (!empty($from_client_id)) {
            $clientModel = Client::loadModel($from_client_id);
        }
        $model = new InternalTransfer([
            'from_account_id' => $from_account_id,
            'to_account_id' => $to_account_id,
            'from_client_id' => $from_client_id,
            'to_client_id' => $to_client_id,
            'currency' => SystemSettings::getDefaultCurrency(),
            'transaction_status' => TransactionConstants::TRANSACTION_STATUS_COMPLETED,
        ]);
        if (empty($model->to_client_id)) {
            $model->to_client_id = $model->from_client_id;
        }
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();

                return $model;
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new HttpException(500, $e->getMessage());
            }

        }

        return $model;
    }
}
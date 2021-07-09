<?php


namespace api\modules\v1\modules\product\controllers;


use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\conf\settings\SystemSettings;
use backend\modules\product\models\Product;
use common\helpers\Lang;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;

class ProductController extends Controller
{
    public function init()
    {
        $this->modelClass = Product::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($org_id = null, $code = null, $name = null, $is_loan = null, $id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['org_id' => $org_id];
        $searchModel = Product::searchModel([
            'defaultOrder' => ['code' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->is_active = 1;
        $searchModel->id = $id;
        $searchModel->code = $code;
        $searchModel->name = $name;
        $searchModel->is_loan = $is_loan;

        return $searchModel->search();
    }

    public function actionCreate($org_id = null, $is_loan = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $model = new Product([
            'org_id' => $org_id,
            'is_active' => 1,
            'is_loan' => (int)$is_loan
        ]);

        $model->setDefaultValues();

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $settingModel = null;
        if ($model->is_loan){
            $model->loanSettingsModel->load(\Yii::$app->request->post('loanProductSettings'), '');
            $settingModel = $model->loanSettingsModel;
        }
        else {
            $model->savingSettingsModel->load(\Yii::$app->request->post('savingProductSettings'), '');
            $settingModel = $model->savingSettingsModel;
        }

        if ($model->validate() && $settingModel->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(201);
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e->getTraceAsString());
                throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
            }
        } elseif (!$model->hasErrors() && !$settingModel->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        if ($settingModel->hasErrors()) {
            $model->addErrors($settingModel->getErrors());
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $model = Product::loadModel(['id' => $id, 'org_id' => $org_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        $settingModel = null;
        if ($model->is_loan){
            $model->loanSettingsModel->load(\Yii::$app->request->post('loanProductSettings'), '');
            $settingModel = $model->loanSettingsModel;
        }
        else {
            $model->savingSettingsModel->load(\Yii::$app->request->post('savingProductSettings'), '');
            $settingModel = $model->savingSettingsModel;
        }

        if ($model->validate() && $settingModel->validate()) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $model->save(false);
                $transaction->commit();
                $response = \Yii::$app->getResponse();
                $response->setStatusCode(200);
            } catch (\Exception $e) {
                $transaction->rollBack();
                \Yii::error($e->getTraceAsString());
                throw new HttpException(500, Lang::t('ERROR: Could not save the transaction. All changes rolled back.'));
            }
        } elseif (!$model->hasErrors() && !$settingModel->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        if ($settingModel->hasErrors()) {
            $model->addErrors($settingModel->getErrors());
        }

        return $model;
    }

}
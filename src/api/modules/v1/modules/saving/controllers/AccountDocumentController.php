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
use backend\modules\saving\Constants;
use backend\modules\saving\models\AccountDocument;
use common\helpers\FileManager;
use common\widgets\fineuploader\UploadHandler;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class AccountDocumentController extends Controller
{
    public function init()
    {
        $this->modelClass = AccountDocument::class;
        $this->resource = Constants::RES_PRODUCT_SAVING;

        parent::init();
    }

    public function actionIndex($account_id)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $searchModel = AccountDocument::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'with' => ['documentType'],
        ]);
        $searchModel->account_id = $account_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return AccountDocument::loadModel($id);
    }

    public function actionCreate($account_id)
    {
        $model = new AccountDocument(['account_id' => $account_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;
        if (!empty($files) && isset($files['tmp_file'])){
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_file');
            if ($uploaded['success']){
                // set tmp_file
                $model->tmp_file = $uploaded['path'];
            }
            else {
                $model->addError('tmp_file', 'File not uploaded : ' . $uploaded['error']);
            }
        }
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    protected function uploadFile($inputName)
    {
        $uploader = new UploadHandler();
        $uploader->inputName = $inputName;// matches Fine Uploader's default inputName value by default
        if (Yii::$app->request->isPost) {
            // upload file
            $tmp_dir = FileManager::getTempDir();
            $result = $uploader->handleUpload($tmp_dir);
            if (isset($result['success']) && $result['success'] == true) {
                $file_name = $uploader->getName();
                $uuid = $result['uuid'];
                $result['path'] = $tmp_dir . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $file_name;
                $result['url'] = Yii::$app->request->getBaseUrl() . '/uploads/tmp/' . $uuid . '/' . $file_name;

            }
            if (isset($result['error'])){
                $result['success'] = false;
            }
            return $result;
        }
    }

    public function actionUpdateDocument($id)
    {
        $model = AccountDocument::loadModel($id);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;
        if (!empty($files) && isset($files['tmp_file'])){
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_file');
            if ($uploaded['success']){
                // set tmp_file
                $model->tmp_file = $uploaded['path'];
            }
            else {
                $model->addError('tmp_file', 'File not uploaded : ' . $uploaded['error']);
            }
        }
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }
}
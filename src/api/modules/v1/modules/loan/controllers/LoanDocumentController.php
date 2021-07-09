<?php


namespace api\modules\v1\modules\loan\controllers;


use api\controllers\Controller;
use backend\modules\auth\Session;
use backend\modules\loan\Constants;
use backend\modules\loan\models\LoanDocument;
use common\helpers\DateUtils;
use common\helpers\FileManager;
use common\widgets\fineuploader\UploadHandler;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class LoanDocumentController extends Controller
{
    public function init()
    {
        $this->modelClass = LoanDocument::class;
        $this->resource = Constants::RES_LOAN;
        parent::init();
    }

    protected function uploadFile($inputName)
    {
        $uploader = new UploadHandler();
        $uploader->inputName = $inputName;
        if (\Yii::$app->request->isPost) {
            $tmp_dir = FileManager::getTempDir();
            $result = $uploader->handleUpload($tmp_dir);

            if (isset($result['success']) && $result['success'] == true) {
                $file_name = $uploader->getName();
                $uuid = $result['uuid'];
                $result['path'] = $tmp_dir . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $file_name;
                $result['url'] = \Yii::$app->request->getBaseUrl() . '/uploads/tmp/' . $uuid . '/' . $file_name;
            }

            if (isset($result['error'])) {
                $result['success'] = false;
            }

            return $result;
        }
    }

    public function actionIndex($loan_id)
    {
        $org_id = null;

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }

        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $searchModel = LoanDocument::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
        ]);
        $searchModel->loan_id = $loan_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return LoanDocument::loadModel($id);
    }

    public function actionCreate($loan_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $model = new LoanDocument([
            'loan_id' => $loan_id,
            'document_date' => DateUtils::getToday()
        ]);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;

        if (!empty($files) && isset($files['tmp_file'])) {
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_file');

            if ($uploaded['success']) {
                $model->tmp_file = $uploaded['path'];
            } else {
                $model->addError('tmp_file', 'File Not Uploaded: ' . $uploaded['error']);
            }
        }

        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reasons');
        }

        return $model;
    }

    public function actionUpdateDoc($id)
    {
        $model = LoanDocument::loadModel($id);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;

        if (!empty($files) && isset($files['tmp_file'])) {
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_file');

            if ($uploaded['success']) {
                $model->tmp_file = $uploaded['path'];
            } else {
                $model->addError('tmp_file', 'File Not Uploaded: ' . $uploaded['error']);
            }
        }

        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to upload the object for unknown reasons');
        }

        return $model;
    }

    public function actionDownload($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $model = LoanDocument::loadModel($id);

        return \Yii::$app->response->sendFile($model->getFilePath(), $model->getDownloadName());
    }
}
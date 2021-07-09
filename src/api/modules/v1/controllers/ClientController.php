<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\modules\v1\models\Client;
use api\controllers\Controller;
use backend\modules\core\models\ClientBankAccount;
use backend\modules\core\models\ClientDocument;
use backend\modules\core\models\ClientKin;
use backend\modules\core\models\ClientKinDocument;
use backend\modules\core\models\ClientResidence;
use backend\modules\core\models\ClientWorkInformation;
use backend\modules\core\models\Organization;
use backend\modules\saving\models\AccountKin;
use common\excel\PHPExcelHelper;
use common\helpers\DateUtils;
use common\helpers\FileManager;
use common\models\Model;
use common\widgets\fineuploader\UploadHandler;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class ClientController extends Controller
{
    public function init()
    {
        $this->modelClass = Client::class;
        $this->resource = \backend\modules\core\Constants::RES_CLIENT;

        parent::init();
    }

    public function actionIndex($org_id = null, $id = null,  $code = null, $full_name = null, $phone = null, $gender_id = null, $identity_type_id = null,
                                $national_identity = null, $country_id = null, $status = Client::STATUS_ACTIVE, $account_type = null, $is_non_member = 0, $from = null, $to = null)
    {
        $this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $date_filter = DateUtils::getDateFilterParams($from, $to, 'date_joined', false, false);
        $condition = $date_filter['condition'];
        $params = [];
        $searchModel = Client::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'with' => ['org', 'gender', 'branch', 'identityType', 'country'],
            'condition' => $condition,
            'params' => $params,
        ]);
        $searchModel->country_id = $country_id;
        $searchModel->code = $code;
        $searchModel->full_name = $full_name;
        $searchModel->phone = $phone;
        $searchModel->id = $id;
        $searchModel->identity_type_id = $identity_type_id;
        $searchModel->national_identity = $national_identity;
        $searchModel->status = $status;
        $searchModel->account_type = $account_type;
        $searchModel->is_non_member = $is_non_member;
        $searchModel->org_id = $org_id;
        $searchModel->gender_id = $gender_id;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Client::loadModel($id);
    }

    public function actionCreate()
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        //TODO: everything in one json payload
        throw new ServerErrorHttpException('Not Implemented', 501);
    }

    public function actionCreateBasic()
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new Client();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;
        if (!empty($files) && isset($files['tmp_passport_photo'])){
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_passport_photo');
            if ($uploaded['success']){
                // set tmp_file
                $model->tmp_passport_photo = $uploaded['path'];
            }
            else {
                $model->addError('tmp_passport_photo', 'File not uploaded : ' . $uploaded['error']);
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

    public function actionCreateBankAccounts($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }

        $post = Yii::$app->request->post();
        $models = [];
        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                $newModel = new ClientBankAccount([
                    'client_id' => $client_id,
                    'is_active' => 1,
                ]);
                if (is_array($item) && !empty($item)) {
                    $newModel->attributes = $item;
                }
                $models[] = $newModel;
            }
        }

        $validModels = [];
        $invalidModels = [];
        $savedModels = [];
        $errorModels = [];

        foreach ($models as $k => $model){
            /* @var $model \common\models\ActiveRecord */
            if($model->validate()){
                $validModels[] = $model;
            }
            else {
                $invalidModels[$k] = $model;
            }
        }
        // try to save if all models are valid

        if (count($validModels) == count($models)){
            foreach ($models as $k => $model){
                /* @var $model \common\models\ActiveRecord */
                if($model->save()){
                    $savedModels[] = $model;
                }
                else {
                    $errorModels[$k] = $model;
                }
            }
        }

        if (count($invalidModels)){
            return $invalidModels;
        }
        if (count($errorModels)){
            return $errorModels;
        }
        return $savedModels;
    }

    public function actionCreateKin($client_id, $account_id = null)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ClientKin([
            'client_id' => $client_id,
            'is_active' => 1,
            'is_child' => 0,
        ]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;
        if (!empty($files) && isset($files['tmp_passport_photo'])){
            // upload file to temp folder
            $uploaded = $this->uploadFile('tmp_passport_photo');
            if ($uploaded['success']){
                // set tmp_file
                $model->tmp_passport_photo = $uploaded['path'];
            }
            else {
                $model->addError('tmp_passport_photo', 'File not uploaded : ' . $uploaded['error']);
            }
        }
        if ($model->validate()) {
            if($model->save()){
                if (!empty($account_id)) {
                    $accountKinModel = new AccountKin(['account_id' => $account_id, 'kin_id' => $model->id]);
                    $accountKinModel->save();
                }
            }
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionCreateWorkInformation($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ClientWorkInformation();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $model->client_id = $client_id;
        $model->is_active = 1;
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionCreateResidenceInformation($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ClientResidence([
            'client_id' => $client_id,
            'is_active' => 1,
        ]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionCreateDocument($client_id){
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ClientDocument(['client_id' => $client_id]);
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

    public function actionCreateKinDocument($client_id, $kin_id){
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = new ClientKinDocument([
            'client_id' => $client_id,
            'kin_id' => $kin_id
        ]);
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

    public function actionUploadPassportPhoto($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
    }

    public function actionUploadKinPassportPhoto($client_id, $kin_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
    }

    public function actionUpdate()
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        //TODO: everything in one json payload
        throw new ServerErrorHttpException('Not Implemented', 501);
    }

    public function actionUpdateBasic($client_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = Client::loadModel($client_id);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdateBankAccount($client_id, $id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientBankAccount::loadModel(['id' => $id, 'client_id' => $client_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;

    }

    public function actionUpdateKin($client_id, $kin_id, $account_id = null)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientKin::loadModel(['id' => $kin_id, 'client_id' => $client_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            if($model->save()){
                if (!empty($account_id)) {
                    $accountKinModel = AccountKin::loadModel($account_id);
                    $accountKinModel->save();
                }
            }
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdateWorkInformation($client_id, $id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientWorkInformation::loadModel(['client_id' => $client_id, 'id' => $id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdateResidenceInformation($client_id, $id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientResidence::loadModel(['id' => $id, 'client_id' => $client_id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate()) {
            $model->save();
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdateDocument($client_id, $doc_id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientDocument::loadModel(['id' => $doc_id, 'client_id' => $client_id]);
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

    public function actionUpdateKinDocument($client_id, $id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        $model = ClientKinDocument::loadModel(['client_id' => $client_id, 'id' => $id]);
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');
        $files = $_FILES;
        if (!empty($files) && isset($files['tmp_file'])) {
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
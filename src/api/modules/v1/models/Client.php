<?php

namespace api\modules\v1\models;


use common\helpers\Url;
use Yii;

class Client extends \backend\modules\core\models\Client {

    public function fields()
    {
        $fields = parent::fields();

        // add or override fields to be returned in API
        // when documenting, list all available fields and document that specific fields can be requested in the url
        // e.g /api/client?fields=id,first_name -> will return only id and name in the collection
        $fields['passport_photo'] = function (Client $model) {
            return $model->getPassportPhotoUrl();
        };
        return $fields;
    }

    public function extraFields()
    {
        return [
            'clientBankAccounts', 'group', 'branch', 'clientDocuments', 'clientKins', 'clientWorkInformation',
            'religion', 'educationLevel', 'maritalStatus', 'gender', 'identityType'
        ];
    }

    public function getPassportPhotoUrl()
    {
        $file_path = $this->getPassportPhotoPath();
        if (empty($file_path)) {
            return null;
        }
        //$url = Yii::getAlias('@appRoot') . '/uploads/organizations/' . $this->org->code . '/clients/' . $this->code . '/' . $this->passport_photo;
        $asset = Yii::$app->getAssetManager()->publish($file_path);

        return Url::to('/', true) .$asset[1];
    }
}

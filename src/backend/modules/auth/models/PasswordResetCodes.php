<?php

namespace backend\modules\auth\models;

use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\settings\SystemSettings;
use common\components\Settings;
use common\helpers\Lang;
use common\helpers\Security;
use common\models\ActiveRecord;
use console\jobs\SendEmailJob as SendEmail;
use Yii;

/**
 * This is the model class for table "auth_password_reset_codes".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $reset_code
 * @property integer $is_active
 * @property integer $created_at
 */
class PasswordResetCodes extends ActiveRecord
{
    public $enableAuditTrail = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_password_reset_codes}}';
    }

    /**
     * Creates a new password reset code
     * @param $username
     * @return PasswordResetCodes|bool
     * @throws \yii\web\NotFoundHttpException
     */
    public static function createCode($username)
    {
        $condition = '([[username]]=:username OR [[email]]=:username)';
        $params = [':username' => $username];
        /* @var $userModel Users */
        $userModel = Users::find()->andWhere($condition, $params)->one();

        if (null === $userModel) {
            return false;
        }

        $model = new PasswordResetCodes([
            'user_id' => $userModel->id,
            'reset_code' => static::generateRandomCode(),
            'is_active' => 1,
            'created_at' => time(),
        ]);

        $model->save(false);

        //send email
        static::queueEmail($model);

        return $model;
    }

    /**
     * @return int
     */
    public static function generateRandomCode()
    {
        return Security::generateRandomInt(1000, 999999);
    }

    /**
     * @param PasswordResetCodes $model
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    private static function queueEmail($model)
    {
        $template_id = 'api_reset_password_verification_code';
        $template = EmailTemplate::getOneRow('*', ['id' => $template_id]);

        if (empty($template))
            return false;

        /* @var $setting \common\components\Settings */
        $setting = Yii::$app->settings;
        $app_name = $setting->get(SystemSettings::SECTION_SYSTEM, SystemSettings::KEY_APP_NAME, Yii::$app->name);
        //placeholders: {{name}},{{code}},
        /* @var $user Users */
        $user = Users::loadModel($model->user_id);
        $message = strtr($template['body'], [
            '{{name}}' => $user->name,
            '{{code}}' => $model->reset_code,
        ]);

        return SendEmail::push([
            'message' => $message,
            'subject' => $template['subject'],
            'sender_name' => $app_name,
            'sender_email' => $template['sender'],
            'recipient_email' => $user->email,
            'template_id' => $template_id,
            'ref_id' => $model->id,
        ]);
    }

    /**
     * @param $code
     * @return bool|int
     */
    public static function isValid($code)
    {
        /* @var $model PasswordResetCodes */
        $model = static::find()->andWhere(['reset_code' => $code, 'is_active' => 1])->one();
        if (null === $model)
            return false;

        $timestamp = (int)$model->created_at;
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        if ($timestamp + $expire >= time()) {
            return $model->user_id;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'reset_code', 'created_at'], 'required'],
            [['user_id', 'reset_code', 'is_active', 'created_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'user_id' => Lang::t('User ID'),
            'reset_code' => Lang::t('Reset Code'),
            'is_active' => Lang::t('Active'),
            'created_at' => Lang::t('Created At'),
        ];
    }

    public function beforeSave($insert)
    {
        $this->updateAll(['is_active' => 0], ['user_id' => $this->user_id]);
        //return parent::beforeSave($insert);
        return true;
    }
}
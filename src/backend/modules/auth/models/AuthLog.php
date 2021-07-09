<?php

namespace backend\modules\auth\models;

use common\models\ActiveRecord;
use Yii;

/**
 * This is the model class for table "auth_log".
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $date
 * @property integer $cookieBased
 * @property integer $duration
 * @property string $error
 * @property string $ip
 * @property string $host
 * @property string $url
 * @property string $userAgent
 */
class AuthLog extends ActiveRecord
{
    public $enableAuditTrail = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'date', 'cookieBased', 'duration'], 'integer'],
            [['error', 'ip', 'host', 'url', 'userAgent'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userId' => 'User',
            'date' => 'Date',
            'cookieBased' => 'Cookie Based',
            'duration' => 'Duration',
            'error' => 'Error',
            'ip' => 'Ip',
            'host' => 'Host',
            'url' => 'Url',
            'userAgent' => 'User Agent',
        ];
    }
}

<?php

namespace backend\modules\conf\models;

use backend\modules\core\models\Organization;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use console\jobs\SendSmsJob;

/**
 * This is the model class for table "sms_outbox".
 *
 * @property int $id
 * @property string $msisdn
 * @property string $message
 * @property string $sender_id
 * @property int $send_status
 * @property int $response_code
 * @property string $response_remarks
 * @property integer $attempts
 * @property string $created_at
 * @property integer $org_id
 * @property int $created_by
 *
 * @property Organization $org
 */
class SmsOutbox extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    const SEND_STATUS_SUCCESS = 1;
    const SEND_STATUS_FAILED = 0;

    const DEFAULT_SENDER_ID = 'ECLECTICS';
    const DEFAULT_TRANSACTION_ID = '5678889999';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_outbox}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['msisdn', 'message', 'sender_id', 'send_status'], 'required'],
            [['send_status', 'attempts', 'org_id'], 'integer'],
            [['msisdn', 'response_code'], 'string', 'max' => 15],
            [['message', 'response_remarks'], 'string', 'max' => 1000],
            [['sender_id'], 'string', 'max' => 20],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'msisdn' => Lang::t('Phone'),
            'message' => Lang::t('Message'),
            'sender_id' => Lang::t('Sender ID'),
            'send_status' => Lang::t('Status'),
            'response_code' => Lang::t('Response Code'),
            'response_remarks' => Lang::t('Remarks'),
            'attempts' => Lang::t('Attempts'),
            'created_at' => Lang::t('Created At'),
            'org_id' => Lang::t('Organization'),
            'created_by' => Lang::t('Created By'),
        ];
    }

    /**
     * @param string $msisdn
     * @param string $message
     * @param null|string $sender_id
     * @param null|string $transaction_id
     */
    public static function sendSms($msisdn, $message, $sender_id = null, $transaction_id = null)
    {
        if (empty($sender_id)) {
            $sender_id = self::DEFAULT_SENDER_ID;
        }
        if (empty($transaction_id)) {
            $transaction_id = self::DEFAULT_TRANSACTION_ID;
        }

        SendSmsJob::push([
            'sender_id' => $sender_id,
            'msisdn' => $msisdn,
            'message' => $message,
            'transaction_id' => $transaction_id,

        ]);
    }

    /**
     * @param $id
     * @throws \yii\web\NotFoundHttpException
     */
    public static function resendSms($id)
    {
        $model=static::loadModel($id);
        if (empty($transaction_id)) {
            $transaction_id = self::DEFAULT_TRANSACTION_ID;
        }

        SendSmsJob::push([
            'sender_id' => $model->sender_id,
            'msisdn' => $model->msisdn,
            'message' => $model->message,
            'transaction_id' => $transaction_id,
            'sms_id' => $id,
            'org_id' => $model->org_id,

        ]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            ['msisdn', 'msisdn'],
            'send_status',
            'attempts',
            'org_id',
        ];
    }

    /**
     * @param int $intVal
     * @return null|string
     */
    public static function decodeSendStatus($intVal)
    {
        $stringVal = null;
        switch ($intVal) {
            case self::SEND_STATUS_SUCCESS:
                $stringVal = 'Success';
                break;
            case self::SEND_STATUS_FAILED:
                $stringVal = 'Failed';
                break;
        }

        return $stringVal;
    }

    /**
     * @param bool $tip
     * @return array
     */
    public static function sendStatusOptions($tip = false)
    {
        return Utils::appendDropDownListPrompt([
            self::SEND_STATUS_SUCCESS => static::decodeSendStatus(self::SEND_STATUS_SUCCESS),
            self::SEND_STATUS_FAILED => static::decodeSendStatus(self::SEND_STATUS_FAILED),
        ], $tip);
    }
}

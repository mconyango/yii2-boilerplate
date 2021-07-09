<?php

namespace backend\modules\conf\models;

use backend\modules\core\models\Organization;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use console\jobs\SendEmailJob;
use Yii;

/**
 * This is the model class for table "email_outbox".
 *
 * @property integer $id
 * @property string $message
 * @property string $subject
 * @property string $sender_name
 * @property string $sender_email
 * @property string $recipient_email
 * @property string $attachment
 * @property string $cc
 * @property string $bcc
 * @property integer $template_id
 * @property integer $status
 * @property integer $ref_id
 * @property string $date_queued
 * @property string $date_sent
 * @property integer $created_by
 * @property integer $org_id
 * @property integer $attempts
 *
 * @property Organization $org
 */
class EmailOutbox extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email_outbox}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message', 'sender_email', 'recipient_email'], 'required'],
            [['message'], 'string'],
            [['template_id', 'ref_id', 'org_id'], 'integer'],
            [['subject'], 'string', 'max' => 255],
            [['sender_name'], 'string', 'max' => 60],
            [['sender_email', 'recipient_email'], 'email'],
            [['attachment', 'cc', 'bcc'], 'safe'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('#'),
            'message' => Lang::t('Message'),
            'subject' => Lang::t('Subject'),
            'sender_name' => Lang::t('Sender Name'),
            'sender_email' => Lang::t('From'),
            'recipient_name' => Lang::t('Recipient Name'),
            'attachment' => Lang::t('Attachment'),
            'cc' => Lang::t('Cc'),
            'bcc' => Lang::t('Bcc'),
            'recipient_email' => Lang::t('To'),
            'created_at' => Lang::t('Queued At'),
            'created_by' => Lang::t('Created By'),
            'org_id' => Lang::t('Organization'),
            'date_sent' => Lang::t('Time Sent'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    public function searchParams()
    {
        return [
            ['recipient_email', 'recipient_email'],
            ['sender_email', 'sender_email'],
            ['subject', 'subject'],
            'template_id',
            'org_id',
        ];
    }

    /**
     * @param $intValue
     * @return null|string
     */
    public static function decodeStatus($intValue)
    {
        $stringValue=null;
        switch ($intValue){
            case self::STATUS_SUCCESS:
                $stringValue='SUCCESS';
                break;
            case self::STATUS_FAILED:
                $stringValue='FAILED';
                break;
        }

        return $stringValue;
    }

    /**
     * @param bool|string $tip
     * @return array
     */
    public static function statusOptions($tip=false)
    {
        return Utils::appendDropDownListPrompt([
            self::STATUS_SUCCESS=>static::decodeStatus(self::STATUS_SUCCESS),
            self::STATUS_FAILED=>static::decodeStatus(self::STATUS_FAILED),
        ],$tip);
    }

    public function resendEmail($id){
        $model=static::loadModel($id);


        SendEmailJob::push([
            'sender_email' => $model->sender_email,
            'sender_name' => $model->sender_name,
            'message' => $model->message,
            'recipient_email' => $model->recipient_email,
            'cc' => $model->cc,
            'bcc' => $model->bcc,
            'attachment' => $model->attachment,
            'subject' => $model->subject,
            'template_id' => $model->template_id,
            'ref_id' => $model->ref_id,
            'org_id' => $model->org_id,
        ]);
    }
}

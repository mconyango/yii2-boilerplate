<?php

namespace backend\modules\auth\models;

use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use common\models\ActiveRecord;
use common\widgets\lineItem\LineItem;
use common\widgets\lineItem\LineItemModelInterface;
use common\widgets\lineItem\LineItemTrait;
use yii\bootstrap\Html;

/**
 * This is the model class for table "auth_users_notification_settings".
 *
 * @property int $id
 * @property int $user_id
 * @property string $notification_id
 * @property int $enable_internal_notification
 * @property int $enable_email_notification
 * @property int $enable_sms_notification
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Users $user
 * @property NotifTypes $notification
 */
class UsersNotificationSettings extends ActiveRecord implements LineItemModelInterface
{
    use LineItemTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_users_notification_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'notification_id'], 'required'],
            [['user_id', 'enable_internal_notification', 'enable_email_notification', 'enable_sms_notification'], 'integer'],
            [['notification_id'], 'string', 'max' => 60],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'user_id' => Lang::t('User'),
            'notification_id' => Lang::t('Notification'),
            'enable_internal_notification' => Lang::t('Enable in-app notification'),
            'enable_email_notification' => Lang::t('Enable Email notification'),
            'enable_sms_notification' => Lang::t('Enable SMS notification'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotification()
    {
        return $this->hasOne(NotifTypes::class, ['id' => 'notification_id']);
    }

    /**
     *  {@inheritdoc}
     */
    public function lineItemFields()
    {
        return [
            [
                'attribute' => 'notification_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_STATIC,
                'value' => function (UsersNotificationSettings $model) {
                    return '<span>' . Html::encode($model->notification->name) . '<br/><small class="text-muted">' . Html::encode($model->notification->description) . '</small>' . '</span>';
                },
                'tdOptions' => ['style' => 'max-width:400px;'],
                'options' => [],
            ],
            [
                'attribute' => 'notification_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT,
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'user_id',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_HIDDEN_INPUT,
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'enable_internal_notification',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'enable_email_notification',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => [],
                'options' => [],
            ],
            [
                'attribute' => 'enable_sms_notification',
                'type' => LineItem::LINE_ITEM_FIELD_TYPE_CHECKBOX,
                'tdOptions' => [],
                'options' => [],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function lineItemFieldsLabels()
    {
        return [
            ['label' => $this->getAttributeLabel('notification_id'), 'options' => []],
            ['label' => $this->getAttributeLabel('enable_internal_notification'), 'options' => []],
            ['label' => $this->getAttributeLabel('enable_email_notification'), 'options' => []],
            ['label' => $this->getAttributeLabel('enable_sms_notification'), 'options' => []],
            ['label' => '&nbsp;', 'options' => []],
        ];
    }

    /**
     * @param int $user_id
     * @param string $notification_id
     * @return UsersNotificationSettings|null
     */
    public static function getNotificationSettings($user_id, $notification_id)
    {
        return static::findOne(['user_id' => $user_id, 'notification_id' => $notification_id]);
    }

    /**
     * @param int $user_id
     * @param string $notification_id
     * @return bool|int
     */
    public static function isInternalNotifEnabled($user_id, $notification_id)
    {
        $model = static::getNotificationSettings($user_id, $notification_id);
        if (null === $model) {
            return true;
        }
        return $model->enable_internal_notification;
    }

    /**
     * @param int $user_id
     * @param string $notification_id
     * @return bool|int
     */
    public static function isEmailNotifEnabled($user_id, $notification_id)
    {
        $model = static::getNotificationSettings($user_id, $notification_id);
        if (null === $model) {
            return true;
        }
        return $model->enable_email_notification;
    }

    /**
     * @param int $user_id
     * @param string $notification_id
     * @return bool|int
     */
    public static function isSmsNotifEnabled($user_id, $notification_id)
    {
        $model = static::getNotificationSettings($user_id, $notification_id);
        if (null === $model) {
            return true;
        }
        return $model->enable_sms_notification;
    }
}

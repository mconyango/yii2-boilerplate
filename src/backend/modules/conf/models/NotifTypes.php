<?php

namespace backend\modules\conf\models;

use backend\modules\core\models\Organization;
use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;

/**
 * This is the model class for table "conf_notif_types".
 *
 * @property int $id
 * @property string $template_id
 * @property integer $org_id
 * @property string $name
 * @property string $description
 * @property string $template
 * @property string $email_template_id
 * @property string $sms_template_id
 * @property integer $enable_internal_notification
 * @property integer $enable_email_notification
 * @property integer $enable_sms_notification
 * @property integer $notify_all_users
 * @property integer $notify_days_before
 * @property string $model_class_name
 * @property string $fa_icon_class
 * @property string $notification_trigger
 * @property integer $max_notifications
 * @property string $notification_time
 * @property integer $is_active
 * @property string $users
 * @property string $roles
 * @property string $email
 * @property string $phone
 * @property string $created_at
 * @property integer $created_by
 *
 * @property Organization $org
 */
class NotifTypes extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    //notification_trigger
    const TRIGGER_MANUAL = '1';
    const TRIGGER_SYSTEM = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_notif_types}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id', 'name', 'model_class_name'], 'required'],
            [['email_template_id', 'sms_template_id'], 'string'],
            [['enable_internal_notification', 'enable_email_notification', 'enable_sms_notification', 'notify_all_users', 'notify_days_before', 'is_active', 'notification_trigger'], 'integer'],
            [['template_id', 'model_class_name'], 'string', 'max' => 128],
            [['name'], 'string', 'max' => 128],
            [['description'], 'string', 'max' => 255],
            [['template'], 'string', 'max' => 500],
            [['email', 'phone'], 'string', 'max' => 1000],
            [['users', 'roles'], 'safe'],
            [['org_id'], 'integer'],
            ['template', 'required', 'when' => function (NotifTypes $model) {
                return $model->enable_internal_notification == true;
            }, 'whenClient' => "function (attribute, value) {
                   return $('#enable_internal_notification').val() == 1;
              }"
            ],
            [['fa_icon_class'], 'string', 'max' => 30],
            [['max_notifications'], 'integer', 'min' => 1],
            ['template_id', 'unique', 'targetAttribute' => ['org_id', 'template_id'], 'message' => 'You already have copy of this template for your organization.Please Update it instead of this template'],
            [['notification_time'], 'string', 'min' => 5, 'max' => 8],
            //[[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'name' => Lang::t('Name'),
            'org_id' => Lang::t('Organization'),
            'template_id' => Lang::t('Template ID'),
            'description' => Lang::t('Description'),
            'template' => Lang::t('Internal Notification Template'),
            'email_template_id' => Lang::t('Email Template'),
            'sms_template_id' => Lang::t('SMS Template'),
            'enable_internal_notification' => Lang::t('Enable Internal Notication'),
            'enable_email_notification' => Lang::t('Enable Email Notification'),
            'enable_sms_notification' => Lang::t('Enable SMS Notification'),
            'notify_all_users' => Lang::t('Notify Every Administrator'),
            'notify_days_before' => Lang::t('Notify earlier'),
            'users' => Lang::t('Users'),
            'roles' => Lang::t('Roles'),
            'email' => Lang::t('Email Addresses'),
            'phone' => Lang::t('Phone Numbers'),
            'model_class_name' => Lang::t('Model Class Name'),
            'fa_icon_class' => Lang::t('Font Awesome Icon Class'),
            'notification_trigger' => Lang::t('Notification trigger'),
            'is_active' => Lang::t('Active'),
            'max_notifications' => Lang::t('Maximum Notifications'),
            'notification_time' => Lang::t('Notification Time'),
        ];
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_BEFORE_INSERT]] event when `$insert` is true,
     * or an [[EVENT_BEFORE_UPDATE]] event if `$insert` is false.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeSave($insert)
     * {
     *     if (parent::beforeSave($insert)) {
     *         // ...custom code here...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     * ```
     *
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @return boolean whether the insertion or updating should continue.
     * If false, the insertion or updating will be cancelled.
     */
    public function beforeSave($insert)
    {
        if (!empty($this->users)) {
            $this->users = serialize($this->users);
        }
        if (!empty($this->roles)) {
            $this->roles = serialize($this->roles);
        }
        return parent::beforeSave($insert);
    }

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        if (!empty($this->users)) {
            $this->users = unserialize($this->users);
        }
        if (!empty($this->roles)) {
            $this->roles = unserialize($this->roles);
        }
        parent::afterFind();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }

    /**
     * Get icon for the notification type
     * @param string $id
     * @return string
     */
    public static function getIcon($id)
    {
        $icon = static::getFieldByPk($id, 'fa_icon_class');
        if (empty($icon))
            $icon = 'fa-bell';
        return $icon;
    }

    /**
     *
     * @return array
     */
    public static function notificationTriggerOptions()
    {
        return [
            self::TRIGGER_MANUAL => Lang::t('Manual'),
            self::TRIGGER_SYSTEM => Lang::t('System'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            ['name', 'name'],
            'is_active',
            'org_id',
            'template_id'
        ];
    }
}

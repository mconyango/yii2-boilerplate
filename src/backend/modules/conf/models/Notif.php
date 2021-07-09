<?php

namespace backend\modules\conf\models;

use backend\modules\auth\models\UserLevels;
use backend\modules\auth\models\Users;
use backend\modules\auth\models\UsersNotificationSettings;
use backend\modules\conf\settings\EmailSettings;
use backend\modules\conf\settings\SmsSettings;
use backend\modules\conf\settings\SystemSettings;
use common\helpers\DateUtils;
use common\helpers\Lang;
use common\models\ActiveRecord;
use console\jobs\SendEmailJob;
use console\jobs\SendSmsJob;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "conf_notif".
 *
 * @property integer $id
 * @property string $notif_type_id
 * @property integer $user_id
 * @property integer $item_id
 * @property integer $is_read
 * @property integer $is_seen
 * @property string $created_at
 */
class Notif extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_notif}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notif_type_id', 'user_id', 'item_id'], 'required'],
            [['user_id', 'item_id', 'is_read', 'is_seen'], 'integer'],
            [['notif_type_id'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'notif_type_id' => Lang::t('Notif Type'),
            'user_id' => Lang::t('User'),
            'item_id' => Lang::t('Item'),
            'is_read' => Lang::t('Read'),
            'created_at' => Lang::t('Date'),
        ];
    }

    /**
     * Pushes a new notification
     * @param string $notifTypeId
     * @param int $itemId
     * @param mixed $userIds
     * @param integer $createdBy
     * @param bool $enableInternalNotif
     * @param bool $enableEmailNotif
     * @param bool $enableSmsNotif
     * @param mixed $additionalEmailAddresses
     * @param mixed $additionalPhoneNumbers
     * @return bool
     */
    public static function pushNotif($notifTypeId, $itemId, $userIds = null, $createdBy = null, $enableInternalNotif = null, $enableEmailNotif = null, $enableSmsNotif = null, $additionalEmailAddresses = null, $additionalPhoneNumbers = null)
    {
        try {
            $notifType = NotifTypes::loadModel(['template_id' => $notifTypeId, 'is_active' => 1], false);
            if (null === $notifType) {
                Yii::error(strtr('notif_type_id {id} does not exist', ['{id}' => $notifTypeId]));
                return false;
            }
            $globalUserIds = static::getNotifUsers($notifTypeId);
            if (!empty($userIds) && !is_array($userIds)) {
                $userIds = array_map('trim', explode(',', $userIds));
            } elseif (empty($userIds)) {
                $userIds = [];
            }
            $userIds = array_merge($globalUserIds, $userIds);
            if (is_null($enableInternalNotif) || $enableInternalNotif) {
                $enableInternalNotif = $notifType->enable_internal_notification;
            }
            if (is_null($enableEmailNotif) || $enableEmailNotif) {
                $enableEmailNotif = $notifType->enable_email_notification;
            }
            if (is_null($enableSmsNotif) || $enableSmsNotif) {
                $enableSmsNotif = $notifType->enable_sms_notification;
            }
            //process email internal
            if ($enableInternalNotif) {
                self::processInternal($notifType, $userIds, $itemId, $createdBy);
            }
            //process email
            if ($enableEmailNotif) {
                if (!empty($additionalEmailAddresses) && !is_array($additionalEmailAddresses)) {
                    $additionalEmailAddresses = array_map('trim', explode(',', $additionalEmailAddresses));
                }
                self::processEmail($notifType, $userIds, $itemId, $createdBy, $additionalEmailAddresses);
            }
            //process sms
            if ($enableSmsNotif) {
                if (!empty($additionalPhoneNumbers) && !is_array($additionalPhoneNumbers)) {
                    $additionalPhoneNumbers = array_map('trim', explode(',', $additionalPhoneNumbers));
                }
                self::processSms($notifType, $userIds, $itemId, $createdBy, $additionalPhoneNumbers);
            }
        } catch (\Exception $e) {
            Yii::error($e->getTraceAsString());
            Yii::$app->controller->stdout("{$e->getMessage()}\n");
            Yii::$app->controller->stdout("{$e->getTraceAsString()}\n");
        }
    }

    /**
     *
     * @param NotifTypes $notifType
     * @param array $userIds
     * @param string $itemId
     * @param int|null $createdBy
     * @return void
     * @throws \yii\db\Exception
     */
    private static function processInternal($notifType, $userIds, $itemId, $createdBy = null)
    {
        if (empty($userIds)) {
            return;
        }
        $notif_data = [];
        $created_at = new Expression('NOW()');
        foreach ($userIds as $k => $userId) {
            if (!UsersNotificationSettings::isInternalNotifEnabled($userId, $notifType->id)) {
                continue;
            }
            if (!YII_DEBUG && $userId == $createdBy) {
                //continue;
            }
            $notif_data[] = [
                'notif_type_id' => $notifType->template_id,
                'user_id' => $userId,
                'item_id' => $itemId,
                'created_at' => $created_at,
            ];
        }

        static::insertMultiple($notif_data);
    }

    /**
     *
     * @param NotifTypes $notifType
     * @param array $userIds
     * @param string $itemId
     * @param int|null $createdBy
     * @param array $additionalEmailAddresses
     * @return void
     * @throws \Exception
     */
    private static function processEmail($notifType, $userIds, $itemId, $createdBy = null, $additionalEmailAddresses = [])
    {
        foreach ($userIds as $k => $userId) {
            if (!UsersNotificationSettings::isEmailNotifEnabled($userId, $notifType->id)) {
                unset($userIds[$k]);
                continue;
            }
            if (!YII_DEBUG && $userId == $createdBy) {
                //unset($userIds[$k]);
            }
        }
        $model_class_name = $notifType->model_class_name;
        /* @var $model \console\jobs\NotifInterface */
        $model = new $model_class_name();
        $emailParams = $model->processEmailTemplate($notifType, $itemId);
        if (!empty($notifType->email) && is_string($notifType->email)) {
            $emailAddresses = array_map('trim', explode(',', $notifType->email));
        } else {
            $emailAddresses = [];
        }
        if (!empty($additionalEmailAddresses) && is_array($additionalEmailAddresses)) {
            $emailAddresses = array_merge($emailAddresses, $additionalEmailAddresses);
        }
        if (!empty($emailParams)) {
            static::sendEmail($emailParams, $userIds, $emailAddresses);
        }
    }

    /**
     *
     * @param NotifTypes $notifType
     * @param array $userIds
     * @param string $itemId
     * @param int|null $createdBy
     * @param array $additionalPhoneNumbers
     * @return void
     * @throws \Exception
     */
    private static function processSms($notifType, $userIds, $itemId, $createdBy = null, $additionalPhoneNumbers = [])
    {
        foreach ($userIds as $k => $userId) {
            if (!UsersNotificationSettings::isSmsNotifEnabled($userId, $notifType->id)) {
                unset($userIds[$k]);
                continue;
            }
            if (!YII_DEBUG && $userId == $createdBy) {
                //unset($userIds[$k]);
            }
        }
        $model_class_name = $notifType->model_class_name;
        /* @var $model \console\jobs\NotifInterface */
        $model = new $model_class_name();
        $template = $model->processSmsTemplate($notifType, $itemId);
        if (!empty($notifType->phone) && is_string($notifType->phone)) {
            $phoneNumbers = array_map('trim', explode(',', $notifType->phone));
        } else {
            $phoneNumbers = [];
        }
        if (!empty($additionalPhoneNumbers) && is_array($additionalPhoneNumbers)) {
            $phoneNumbers = array_merge($phoneNumbers, $additionalPhoneNumbers);
        }
        if (!empty($template)) {
            static::sendSms($userIds, $template['message'], $phoneNumbers);
        }
    }

    /**
     * Send notification as an sms
     * @param array $userIds
     * @param string $message
     * @param null|array $phoneNumbers
     * @throws \Exception
     */
    private static function sendSms($userIds, $message, $phoneNumbers = [])
    {
        if (!empty($userIds)) {
            $phoneNumbers = array_merge((array)$phoneNumbers, Users::getColumnData('phone', ['id' => $userIds]));
        }

        if (!empty($phoneNumbers)) {
            $phoneNumbers = array_unique($phoneNumbers);
            foreach ($phoneNumbers as $msisdn) {
                $sms = [
                    'message' => $message,
                    'msisdn' => $msisdn,
                    'sender_id' => SmsSettings::getDefaultSenderId(),
                    'transaction_id' => SmsOutbox::DEFAULT_TRANSACTION_ID,
                ];
                SendSmsJob::push($sms);
            }
        }
    }

    /**
     * Update notification
     * System triggered notifications
     * This method should be run in console application via cron (every hour is the recommended cron frequency)
     */
    public static function createNotifications()
    {
        $rowset = NotifTypes::getColumnData('model_class_name', ['notification_trigger' => NotifTypes::TRIGGER_SYSTEM, 'is_active' => 1]);
        foreach ($rowset as $model_class) {
            /* @var $model \console\jobs\NotifInterface */
            try {
                $model = new $model_class();
                $model->createSystemNotifications();
            } catch (\Exception $e) {
                Yii::error($e->getTraceAsString());
            }
        }
    }

    /**
     * Get notification date
     * @param string $notif_type_id
     * @return string $date
     * @throws \Exception
     */
    public static function getNotificationDate($notif_type_id)
    {
        $notify_days_before = NotifTypes::getFieldByPk($notif_type_id, 'notify_days_before');
        if (empty($notify_days_before)) {
            return date('Y-m-d');
        }

        return DateUtils::addDate(date('Y-m-d'), (int)$notify_days_before, 'day');
    }

    /**
     * Get users who are supposed to receive a notification
     * @param string $notif_type_id
     * @return array $users
     */
    public static function getNotifUsers($notif_type_id)
    {
        $notify_all_users = NotifTypes::getScalar('notify_all_users', ['template_id' => $notif_type_id]);

        if ($notify_all_users)
            return Users::getColumnData('id', 'status=:t1 AND level_id<=:t2', [':t1' => Users::STATUS_ACTIVE, ':t2' => UserLevels::LEVEL_ADMIN]);

        $users = NotifTypes::getScalar('users', ['template_id' => $notif_type_id]);
        if (!empty($users)) {
            $users = unserialize($users);
        } else {
            $users = [];
        }
        $roles = NotifTypes::getScalar('roles', ['template_id' => $notif_type_id]);
        if (!empty($roles)) {
            $users = array_merge($users, Users::getColumnData('id', ['role_id' => unserialize($roles)]));
        }
        return array_unique($users);
    }

    /**
     * Send notification as an email
     * @param array $emailParams
     * @param array $user_ids
     * @param array|string $email_addresses
     */
    private static function sendEmail($emailParams, $user_ids, $email_addresses = null)
    {
        if (!empty($email_addresses) && is_string($email_addresses)) {
            $email_addresses = explode(',', $email_addresses);
        }

        if (!empty($user_ids)) {
            $email_addresses = array_merge((array)$email_addresses, Users::getColumnData('email', ['id' => $user_ids]));
        }

        if (!empty($email_addresses)) {
            $email_addresses = array_unique($email_addresses);
            foreach ($email_addresses as $e) {
                $email = $emailParams;
                $email['recipient_email'] = trim($e);
                $email['host'] = isset($email['host']) ? $email['host'] : EmailSettings::getHost();
                $email['username'] = isset($email['username']) ? $email['username'] : EmailSettings::getUsername();
                $email['password'] = isset($email['password']) ? $email['password'] : EmailSettings::getPassword();
                $email['port'] = isset($email['port']) ? $email['port'] : EmailSettings::getPort();
                $email['security'] = isset($email['security']) ? $email['security'] : EmailSettings::getSecurity();
                SendEmailJob::push($email);
            }
        }
    }

    /**
     * Fetch notification
     * @param string $user_id
     * @return array
     */
    public static function fetchNotif($user_id = NULL)
    {
        if (empty($user_id))
            $user_id = Yii::$app->user->id;
        return static::getData('*', ['user_id' => $user_id], [], ['orderBy' => ['id' => SORT_DESC], 'limit' => 100]);
    }

    /**
     *
     * @param string $user_id
     * @return int
     */
    public static function getTotalUnSeenNotif($user_id = NULL)
    {
        if (empty($user_id))
            $user_id = Yii::$app->user->id;
        return static::getCount(['user_id' => $user_id, 'is_seen' => 0]);
    }

    /**
     *
     * @param string $notif_type_id
     * @param string $item_id
     * @return array|bool $processed_template
     */
    public static function processTemplate($notif_type_id, $item_id)
    {
        $notif_type = NotifTypes::getOneRow('template,model_class_name', ['id' => $notif_type_id]);
        if (empty($notif_type))
            return false;
        $model_class_name = $notif_type['model_class_name'];
        /* @var $model \console\jobs\NotifInterface */
        $model = new $model_class_name();
        return $model::processInternalTemplate($notif_type['template'], $item_id, $notif_type_id);
    }

    /**
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function markAsSeen()
    {
        return Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_seen' => 1], ['user_id' => Yii::$app->user->id])
            ->execute();
    }

    /**
     *
     * @param string $id
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function markAsRead($id = NULL)
    {
        $condition = ['user_id' => Yii::$app->user->id];
        if (!empty($id)) {
            $condition['id'] = $id;
        }

        return Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_read' => 1], $condition)
            ->execute();
    }

}

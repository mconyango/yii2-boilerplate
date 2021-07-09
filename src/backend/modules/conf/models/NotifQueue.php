<?php

namespace backend\modules\conf\models;

use common\helpers\DateUtils;
use common\models\ActiveRecord;

/**
 * This is the model class for table "conf_notif_queue".
 *
 * @property int $id
 * @property string $notif_type_id
 * @property string $item_id
 * @property int $max_notifications
 * @property int $notifications_count
 * @property string $notification_time
 * @property string $created_at
 * @property string $updated_at
 */
class NotifQueue extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%conf_notif_queue}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['notif_type_id', 'item_id'], 'required'],
            [['max_notifications', 'notifications_count'], 'integer'],
            [['notif_type_id', 'item_id', 'notification_time'], 'string', 'max' => 128],
        ];
    }

    /**
     * @param string $notif_type_id
     * @param string $item_id
     * @return bool
     * @throws \yii\web\NotFoundHttpException
     */
    public static function push($notif_type_id, $item_id)
    {
        $model = static::loadModel(['notif_type_id' => $notif_type_id, 'item_id' => $item_id], false);
        $notifModel = NotifTypes::loadModel(['template_id' => $notif_type_id]);

        if ($notifModel === null){
            return false;
        }
        if (null === $model) {
            $model = new self([
                'notif_type_id' => $notif_type_id,
                'item_id' => $item_id,
                'max_notifications' => $notifModel->max_notifications,
                'notification_time' => $notifModel->notification_time,
                'notifications_count' => 1,
            ]);
        } else {
            if ($model->notifications_count >= $model->max_notifications) {
                return false;
            }
            //At most 1 notification per day
            $today = date('Y-m-d');
            $date_updated = DateUtils::formatDate($model->updated_at, 'Y-m-d');
            if ($today == $date_updated) {
                return false;
            }

            $model->notifications_count += 1;
        }
        //check for notification time if set
        if ($notifModel->notification_trigger == NotifTypes::TRIGGER_SYSTEM && !empty($model->notification_time)) {
            $current_local_time = DateUtils::formatToLocalDate(date('Y-m-d H:i:s', time()), 'H:i', true);
            $interval = DateUtils::getDateDiff($current_local_time, $model->notification_time);
            $diff = $interval->h . '.' . $interval->i;
            if ($interval->invert) {
                $diff = -$diff;
            }
            $diff = (float)$diff;

            if ($diff > 0) {
                return false;
            }
        }

        $model->updated_at = DateUtils::mysqlTimestamp();
        if ($model->save(false)) {
            return true;
        }

        return false;
    }
}

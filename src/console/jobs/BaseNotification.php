<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-07-11
 * Time: 22:13
 */

namespace console\jobs;


use backend\modules\conf\models\EmailTemplate;
use backend\modules\conf\models\NotifQueue;
use backend\modules\conf\models\NotifTypes;
use backend\modules\conf\models\SmsTemplate;
use backend\modules\conf\settings\EmailSettings;
use backend\modules\conf\settings\SystemSettings;
use Yii;
use yii\base\BaseObject;

class BaseNotification extends BaseObject
{

    /**
     * @var string
     */
    public $notif_type_id;

    /**
     * @var integer
     */
    public $item_id;

    /**
     * @var integer
     */
    public $created_by;

    /**
     * @param mixed $params
     * @return mixed
     */
    public static function push($params)
    {
        /* @var $queue \yii\queue\cli\Queue */
        $queue = Yii::$app->queue;
        if ($params instanceof static) {
            $obj = $params;
        } else {
            $obj = new static($params);
        }

        $id = $queue->push($obj);

        return $id;
    }

    /**
     * @param array $item_ids
     * @param string $notif_type_id
     * @throws \yii\web\NotFoundHttpException
     */
    protected static function pushCreatedNotification($item_ids, $notif_type_id)
    {
        if (!empty($item_ids)) {
            foreach ($item_ids as $item_id) {
                if (NotifQueue::push($notif_type_id, $item_id)) {
                    //push notification to the queue for processing
                    static::push([
                        'notif_type_id' => $notif_type_id,
                        'item_id' => $item_id,
                        'created_by' => null,
                    ]);
                }
            }
        }
    }

    /**
     * @param string $notif_type_id
     * @param string $item_id
     * @return mixed
     * @throws \yii\web\NotFoundHttpException
     */
    public static function createManualNotifications($notif_type_id, $item_id)
    {
        if (NotifQueue::push($notif_type_id, $item_id)) {
            //push notification to the queue for processing
            static::push([
                'notif_type_id' => $notif_type_id,
                'item_id' => $item_id,
                'created_by' => Yii::$app instanceof \yii\web\Application && !Yii::$app->user->isGuest ? Yii::$app->user->id : null,
            ]);
        }
    }

    /**
     * @param NotifTypes $notifType
     * @param string $itemId
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     */
    public static function processEmailTemplate($notifType, $itemId)
    {
        $emailTemplateModel = EmailTemplate::loadModel(['template_id' => $notifType->email_template_id], false);
        if (null === $emailTemplateModel) {
            return false;
        }

        $emailParams = [
            'sender_name' => SystemSettings::getAppName(),
            'sender_email' => $emailTemplateModel->sender,
            'template_id' => $notifType->email_template_id,
            'ref_id' => $itemId,
        ];
        $params = static::processTemplate($itemId, $emailTemplateModel->body, $emailTemplateModel->subject);
        $emailParams['subject'] = $params['subject'];
        $emailParams['message'] = $params['message'];

        return $emailParams;
    }

    /**
     *
     * @param string $template
     * @param string $item_id
     * @param string $notif_type_id
     *
     * @return array|bool
     * @throws \yii\web\NotFoundHttpException
     */
    public static function processInternalTemplate($template, $item_id, $notif_type_id)
    {
        $notifModel = NotifTypes::loadModel(['template_id' => $notif_type_id], false);
        if (null === $notifModel) {
            return false;
        }
        return static::processTemplate($item_id, $template, $notifModel->name);
    }
}
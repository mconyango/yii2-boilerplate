<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 6:43 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Session;
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use Yii;

class NotifController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Notification Template';
    }

    public function actionIndex()
    {
        $searchModel = NotifTypes::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
        ]);
        $searchModel->is_active = 1;

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        if (!Session::isDev())
            throw new ForbiddenHttpException(Lang::t('403_error'));

        $model = new NotifTypes(['is_active' => 1, 'max_notifications' => 1, 'enable_internal_notification' => 0, 'enable_email_notification' => 0, 'enable_sms_notification' => 0, 'notify_all_users' => 0, 'notification_time' => '08:00', 'fa_icon_class' => 'fa-bell']);
        return $model->simpleSave('create');
    }

    public function actionUpdate($id)
    {
        $model = NotifTypes::loadModel($id);
        return $model->simpleSave('update', 'update');
    }

    public function actionDelete($id)
    {
        NotifTypes::softDelete($id);
    }

    public function actionFetch()
    {
        $html = $this->renderPartial('fetch', [
            'data' => Notif::fetchNotif(),
        ]);
        return Json::encode(['html' => $html, 'unseen' => (int)Notif::getTotalUnSeenNotif(), 'total' => (int)Notif::getCount(['user_id' => Yii::$app->user->id])]);
    }

    public function actionMarkAsRead($id = NULL)
    {
        Notif::markAsRead($id);
        echo Json::encode(TRUE);
    }

    public function actionMarkAsSeen()
    {
        Notif::markAsSeen();
    }
}
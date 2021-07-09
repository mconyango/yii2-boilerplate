<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 6:43 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\conf\models\Notif;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use common\helpers\Url;
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

    public function actionIndex($org_id = null)
    {
        $searchModel = NotifTypes::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
        ]);
        if (Session::isOrganization()) {
            $org_id = [Session::accountId(), null];
        }
        $searchModel->org_id = $org_id;
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
        if (Session::isOrganization()) {
            if (null === $model->org_id) {
                $model->isNewRecord = true;
                $model->id = null;
                $model->org_id = Session::accountId();
            }
            if (empty($success_msg))
                $success_msg = Lang::t('SUCCESS_MESSAGE');

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', $success_msg);
                $redirect_url = Url::to(['index']);
                return Yii::$app->controller->redirect(Url::getReturnUrl($redirect_url));
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        } else {
            return $model->simpleSave('update', 'update');
        }
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
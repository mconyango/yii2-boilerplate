<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 11/11/18
 * Time: 20:28
 */

namespace backend\modules\auth\controllers;


use backend\modules\auth\models\Users;
use backend\modules\auth\models\UsersNotificationSettings;
use backend\modules\auth\Session;
use backend\modules\conf\models\NotifTypes;
use common\helpers\Lang;
use common\widgets\lineItem\LineItem;
use Yii;

class ProfileController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Profile';
        $this->enableDefaultAcl = false;
    }

    public function actionUpdate()
    {
        $model = Users::loadModel(Session::userId());

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('SUCCESS_MESSAGE'));
                return $this->refresh();
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionChangePassword()
    {
        $model = Users::loadModel(Session::userId());
        $model->setScenario(Users::SCENARIO_CHANGE_PASSWORD);
        $model->require_password_change = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash(self::FLASH_SUCCESS, Lang::t('Password changed successfully.'));

            return $this->redirect(['update']);
        }

        return $this->render('changePassword', [
            'model' => $model,
        ]);
    }

    public function actionNotification()
    {
        $user = Users::loadModel(Session::userId());
        $lineItemModelClassName = UsersNotificationSettings::class;
        if ($resp = LineItem::finishAction($user, $lineItemModelClassName, 'user_id', false,[
            'redirectRoute' => 'notification',
        ])) {
            return $resp;
        }
        $models = [];
        foreach (NotifTypes::findAll(['is_active' => 1]) as $n) {
            $model = UsersNotificationSettings::findOne(['user_id' => $user->id, 'notification_id' => $n->id]);
            if ($model === null) {
                $model = new UsersNotificationSettings([
                    'user_id' => $user->id,
                    'notification_id' => $n->id,
                    'enable_internal_notification' => true,
                    'enable_email_notification' => true,
                    'enable_sms_notification' => true,
                ]);
            }
            $models[] = $model;
        }

        return $this->render('notification', [
            'user' => $user,
            'lineItemModels' => $models,
        ]);
    }
}
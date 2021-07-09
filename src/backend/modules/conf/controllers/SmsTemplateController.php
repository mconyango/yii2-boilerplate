<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 25/10/18
 * Time: 18:28
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Session;
use backend\modules\conf\Constants;
use backend\modules\conf\models\SmsTemplate;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;

class SmsTemplateController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->activeSubMenu = Constants::SUBMENU_SMS;
        $this->resourceLabel = 'SMS Template';
    }

    function actions()
    {
        return [
            'settings' => [
                'class' => \yii2mod\settings\actions\SettingsAction::class,
                'modelClass' => \backend\modules\conf\settings\SmsSettings::class,
                'sectionName' => \backend\modules\conf\settings\SmsSettings::SECTION_SMS,
                'view' => 'settings',
            ],
        ];
    }

    public function actionIndex($org_id = null)
    {
        $searchModel = SmsTemplate::searchModel([
            'defaultOrder' => ['code' => SORT_ASC]
        ]);
        if (Session::isOrganization()) {
            $org_id = [Session::accountId(), null];
        }
        $searchModel->org_id = $org_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new SmsTemplate([]);
        return $model->simpleAjaxSave();
    }

    public function actionUpdate($id)
    {
        $model = SmsTemplate::loadModel($id);
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
            return $this->renderPartial('_form', [
                'model' => $model,
            ]);
        } else {
            return $model->simpleAjaxSave();
        }

    }

    public function actionDelete($id)
    {
        SmsTemplate::softDelete($id);
    }
}
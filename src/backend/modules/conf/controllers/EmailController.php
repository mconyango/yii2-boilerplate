<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 5:59 PM
 */

namespace backend\modules\conf\controllers;

use backend\modules\auth\Session;
use backend\modules\conf\Constants;
use backend\modules\conf\models\EmailTemplate;
use common\helpers\Lang;
use common\helpers\Url;
use Yii;

class EmailController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Email Template';
        $this->activeSubMenu = Constants::SUBMENU_EMAIL;
    }

    function actions()
    {
        return [
            'settings' => [
                'class' => \yii2mod\settings\actions\SettingsAction::class,
                'modelClass' => \backend\modules\conf\settings\EmailSettings::class,
                'sectionName' => \backend\modules\conf\settings\EmailSettings::SECTION_EMAIL,
                'view' => 'settings',
            ],
        ];
    }


    public function actionIndex($org_id = null)
    {
        $searchModel = EmailTemplate::searchModel(['defaultOrder' => ['id' => SORT_ASC]]);
        if (Session::isOrganization()) {
            $org_id = [Session::accountId(), null];
            //dd($org_id);
        }
        $searchModel->org_id = $org_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionCreate()
    {
        $model = new EmailTemplate();
        return $model->simpleSave('create');
    }

    public function actionUpdate($id)
    {
        $model = EmailTemplate::loadModel($id);
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
        EmailTemplate::softDelete($id);
    }
}
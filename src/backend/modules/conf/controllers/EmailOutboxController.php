<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/20
 * Time: 12:30 PM
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\conf\Constants;
use backend\modules\conf\models\EmailOutbox;

class EmailOutboxController extends Controller
{
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Email Outbox';
        $this->activeSubMenu = Constants::SUBMENU_EMAIL;
    }

    public function actionIndex($org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $searchModel = EmailOutbox::searchModel(['defaultOrder' => ['id' => SORT_DESC]]);
        $searchModel->org_id = $org_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }


    public function actionView($id)
    {
        $model = EmailOutbox::loadModel($id);

        return $this->renderAjax('view', [
            'model' => $model,
        ]);

    }

    public function actionDelete($id)
    {
        EmailOutbox::softDelete($id);
    }

    public function actionResend($id){
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        EmailOutbox::resendEmail($id);

        return json_encode(['success' => true, 'message' => 'Message has been resent successfully']);
    }
}
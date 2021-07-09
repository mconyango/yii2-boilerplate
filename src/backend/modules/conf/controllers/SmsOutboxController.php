<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 25/10/18
 * Time: 18:50
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use backend\modules\conf\Constants;
use backend\modules\conf\models\SmsOutbox;
use yii\web\NotFoundHttpException;

class SmsOutboxController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->activeSubMenu = Constants::SUBMENU_SMS;
        $this->resourceLabel = 'SMS Outbox';
    }

    public function actionIndex($org_id = null)
    {
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        $searchModel = SmsOutbox::searchModel([
            'defaultOrder' => ['id' => SORT_DESC]
        ]);
        $searchModel->org_id = $org_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    public function actionResend($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        SmsOutbox::resendSms($id);

        return json_encode(['success' => true, 'message' => 'Message has been resent successfully']);
    }

}
<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/05/05
 * Time: 2:42 PM
 */

namespace backend\modules\auth\controllers;


use backend\modules\auth\Constants;
use backend\modules\auth\models\AuditTrail;
use backend\modules\auth\Session;
use backend\modules\core\models\Organization;
use common\helpers\DateUtils;

class AuditTrailController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Audit Trail';
        $this->activeSubMenu = Constants::SUBMENU_AUDIT_TRAIL;
    }

    public function actionIndex($user_id = null, $org_id = null, $action = null, $from = null, $to = null)
    {
        $orgModel = null;
        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (!empty($org_id)) {
            $orgModel = Organization::loadModel($org_id);
        }
        $date_filter = DateUtils::getDateFilterParams($from, $to, 'created_at', true, true);
        $condition = $date_filter['condition'];
        $params = [];
        $searchModel = AuditTrail::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'condition' => $condition,
            'params' => $params,
            'with' => ['user'],
        ]);
        $searchModel->user_id = $user_id;
        $searchModel->org_id = $org_id;
        $searchModel->action = $action;

        return $this->render('index', [
            'filterOptions' => [
                'user_id' => $user_id,
                'org_id' => $org_id,
                'action' => $action,
                'from' => $date_filter['from'],
                'to' => $date_filter['to'],
            ],
            'searchModel' => $searchModel,
            'orgModel' => $orgModel,
        ]);
    }

    public function actionView($id)
    {
        $model = AuditTrail::loadModel($id);

        return $this->renderPartial('view', [
            'model' => $model,
        ]);
    }

}
<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-04-19
 * Time: 12:04 PM
 */

namespace backend\modules\dashboard\controllers;


use backend\modules\auth\Session;
use backend\modules\core\models\Organization;
use backend\modules\subscription\models\OrgSubscription;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    public function actionIndex()
    {
        if (Session::isOrganization()) {
            return $this->redirect(['/core/dashboard/index']);
        } else {
            return $this->redirect(['/core/organization/index']);
        }

        return $this->render('index');
    }

    public function actionStatus()
    {
        $orgModel = Organization::loadModel(Session::accountId());

        $condition = 'status <> :active';
        $params = [
            ':active' => OrgSubscription::STATUS_ACTIVE
        ];

        list($condition, $params) = OrgSubscription::appendOrgSessionIdCondition($condition, $params);
        $searchModel = OrgSubscription::searchModel([
            'defaultOrder' => ['id' => SORT_DESC],
            'condition' => $condition,
            'params' => $params,
        ]);
        $inactiveSubs = $searchModel->search()->getModels();

        $activeSub = OrgSubscription::loadModel(['status' => OrgSubscription::STATUS_ACTIVE, 'org_id' => Session::accountId()], false);

        return $this->render('subscription-status', [
            'inactiveSubs' => $inactiveSubs,
            'orgModel' => $orgModel,
            'activeSub' => $activeSub,
        ]);
    }

    public function actionGraph($graphType = null, $dateRange = null)
    {
        return $this->renderPartial('graph/graph', ['graphType' => $graphType, 'dateRange' => $dateRange]);
    }
}
<?php

namespace backend\modules\auth\controllers;

use backend\modules\auth\Acl;
use backend\modules\auth\Constants;
use backend\modules\auth\models\PermissionLineItems;
use backend\modules\auth\models\Roles;
use backend\modules\auth\models\UserLevels;
use backend\modules\auth\Session;
use backend\modules\core\models\Organization;
use common\widgets\lineItem\LineItem;

/**
 * Roles management controller
 * @author Fred <mconyango@gmail.com>
 */
class RoleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->resourceLabel = 'Role';
        $this->resource = Constants::RES_ROLE;
        $this->activeSubMenu = Constants::SUBMENU_ROLES;
    }


    public function actionIndex($org_id = null)
    {
        $orgModel = null;
        if (Session::isOrganization()) {
            $orgModel = Organization::loadModel(Session::accountId());
            $org_id = Session::accountId();
        }
        $searchModel = Roles::searchModel([
            'defaultOrder' => ['name' => SORT_ASC]
        ]);
        $searchModel->is_active = 1;
        $searchModel->org_id = $org_id;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'orgModel' => $orgModel,
        ]);
    }

    public function actionView($id)
    {
        $this->hasPrivilege(Acl::ACTION_UPDATE);

        $role = Roles::loadModel($id);
        $lineItemModelClassName = PermissionLineItems::class;
        if ($resp = LineItem::finishAction($role, $lineItemModelClassName, 'role_id', false,[])) {
            return $resp;
        }

        return $this->render('view', [
            'role' => $role,
            'lineItemModels' => PermissionLineItems::getModels($role),
        ]);
    }

    public function actionCreate()
    {
        $org_id = null;
        $level_id = null;
        if(Session::isOrganization()){
            $org_id = Session::accountId();
            $level_id = UserLevels::LEVEL_ORGANIZATION;
        }
        $model = new Roles([
            'org_id'=> $org_id,
            'is_active'=> 1,
            'level_id'=>$level_id,
        ]);
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionUpdate($id)
    {
        $model = Roles::loadModel($id);
        return $model->simpleAjaxSaveRenderAjax();
    }

    public function actionDelete($id)
    {
        Roles::loadModel($id)->delete();
    }

    public function actionGetList($level_id)
    {
        $data = Roles::getListData('id', 'name', false, ['level_id' => $level_id]);
        return json_encode($data);
    }
}

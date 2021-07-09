<?php

namespace api\modules\v1\controllers;


use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\models\ClientResidence;
use yii\web\BadRequestHttpException;

class ClientResidenceController extends Controller
{
    public function init()
    {
        $this->modelClass = ClientResidence::class;
        $this->resource = \backend\modules\core\Constants::RES_CLIENT;

        parent::init();
    }

    public function actionIndex($client_id)
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['client_id' => $client_id];
        $searchModel = ClientResidence::searchModel([
            'defaultOrder' => ['id' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->is_active = 1;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return ClientResidence::loadModel($id);
    }

}
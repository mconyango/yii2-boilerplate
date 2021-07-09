<?php


namespace api\modules\v1\modules\loan\controllers;


use api\controllers\Controller;
use backend\modules\core\models\Bank;

class TestController extends Controller
{
    public function init()
    {
        $this->modelClass = Bank::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex()
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);;

        return ['success' => true, 'message' => 'Testing ' . $this->module->id.' submodule successful'];
    }


}
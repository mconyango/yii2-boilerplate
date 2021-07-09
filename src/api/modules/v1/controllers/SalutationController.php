<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\models\Salutation;
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;

class SalutationController extends Controller
{
    public function init()
    {
        $this->modelClass = Salutation::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($org_id = null, $name = null)
    {
        //$this->hasPrivilege(Acl::ACTION_VIEW);

        if (Session::isOrganization()) {
            $org_id = Session::accountId();
        }
        if (empty($org_id)) {
            throw new BadRequestHttpException();
        }

        $condition = ['org_id' => $org_id];
        $searchModel = Salutation::searchModel([
            //'defaultOrder' => ['code' => SORT_ASC],
            'condition' => $condition,
        ]);
        $searchModel->is_active = 1;
        $searchModel->name = $name;

        return $searchModel->search();
    }

    public function actionView($id)
    {
        if (!Session::isOrganization()) {
            throw new BadRequestHttpException();
        }
        return Salutation::loadModel($id);
    }

}
<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\modules\saving\controllers;


use api\controllers\JwtAuthTrait;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use api\controllers\Controller;
use backend\modules\core\TransactionConstants;
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;

class ListOptionsController extends Controller
{
    public function init()
    {
        $this->modelClass = TransactionConstants::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex()
    {
        $data = [];
        $status = TransactionConstants::listOptions();
        foreach ($status as $key => $value) {
            $data[] = [
                'id' => $key,
                'label' => $value,
            ];
        }

        if (Session::isOrganization() || Session::isPrivilegedAdmin()) {
            return $data;
        }
        else{
            throw new BadRequestHttpException();
        }
    }
}
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
use common\helpers\DateUtils;
use yii\web\BadRequestHttpException;

class YearOptionsController extends Controller
{
    public function init()
    {
        $this->modelClass = DateUtils::class;
        $this->resource = \backend\modules\core\Constants::RES_ORG;

        parent::init();
    }

    public function actionIndex($startYear =null )
    {
        $data = [];
        $status = DateUtils::getYearOptions($startYear);
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
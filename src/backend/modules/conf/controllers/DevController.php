<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 24/10/18
 * Time: 22:19
 */

namespace backend\modules\conf\controllers;


use backend\modules\auth\Session;
use common\helpers\Lang;
use yii\web\ForbiddenHttpException;

class DevController extends Controller
{
    public function init()
    {
        parent::init();

        $this->enableDefaultAcl = false;
        $this->enableHelpLink = false;
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (!Session::isDev()) {
                throw new ForbiddenHttpException(Lang::t('403_error'));
            }

            return true;
        }
        return false;
    }

}
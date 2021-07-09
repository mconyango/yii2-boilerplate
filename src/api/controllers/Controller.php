<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-02 7:14 PM
 */

namespace api\controllers;


use api\modules\v1\models\User;
use backend\modules\auth\Acl;
use backend\modules\auth\Session;
use common\helpers\Lang;
use yii\web\ForbiddenHttpException;

class Controller extends ActiveController
{
    use JwtAuthTrait;

    /**
     * Define this in each controller to enable ACL
     * @var string
     */
    public $resource;

    /**
     * Should be called before any action the require ACL
     * @param string $action
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function hasPrivilege($action = null)
    {
        if ($action === null)
            $action = Acl::ACTION_VIEW;

        $identity = Session::userId();

        if(empty($identity))
            throw new ForbiddenHttpException(Lang::t('403_error'));

        Acl::hasPrivilege($this->resource, $action, true);
    }
}
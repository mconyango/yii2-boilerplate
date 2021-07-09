<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/03
 * Time: 1:58 PM
 */

namespace api\modules\v1\controllers;

use api\modules\v1\models\User;
use api\controllers\Controller;

class UserController extends Controller
{
    public function init()
    {
        $this->modelClass = User::class;
        parent::init();
    }

    public function actionView()
    {
        // default to logged in user
        return $this->getAuthToken()->getIdentity();
    }
}
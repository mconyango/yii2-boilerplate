<?php

namespace api\controllers;

use backend\modules\auth\models\UserIdentity;
use backend\modules\auth\models\UserLevels;
use Yii;
use yii\web\ForbiddenHttpException;

trait AuthorizesUser
{

    /**
     * prevent custom /{id} endpoints from moving further than required
     * Since they call GET instead of options, when the rule GET,OPTIONS is provided
     */
    protected function preventOptionsPropagation()
    {
        if (Yii::$app->getRequest()->getMethod() == 'OPTIONS') {
            Yii::$app->getResponse()->getHeaders()->set('Allow',
                implode(', ', ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']));
            Yii::$app->response->send();
            Yii::$app->end();
        }
    }

    /**
     * Require certain level privileges on a method
     *
     * @param $level
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function requireLevel($level)
    {
        $this->preventOptionsPropagation();
        $user = $this->getAuthToken()->getIdentity();
        if ($user->level_id !== $level) {
            throw new ForbiddenHttpException('Access denied.');
        }

        return true;
    }

    /**
     *
     */
    public function requireAuth()
    {
        $this->preventOptionsPropagation();
        return $this->getAuthToken()->getIdentity();
    }

    /**
     * Helper method to require admin user
     * @param bool $returnUser
     * Pass the boolean argument as true, to get back the admin
     *
     * @return bool|UserIdentity
     * @throws ForbiddenHttpException
     */
    public function requireAdmin($returnUser = false)
    {
        $this->preventOptionsPropagation();

        if ($returnUser) {
            return $this->getLoggedInAdmin();
        }
        return $this->requireLevel(UserLevels::LEVEL_ADMIN);
    }

    /**
     * @return \backend\modules\auth\models\UserIdentity
     * @throws ForbiddenHttpException
     */
    public function getLoggedInAdmin()
    {
        $this->preventOptionsPropagation();
        $identity = $this->getAuthToken()->getIdentity();
        if ($identity->level_id !== UserLevels::LEVEL_ADMIN) {
            throw new ForbiddenHttpException('Access denied.');
        }
        return $identity;
    }

}
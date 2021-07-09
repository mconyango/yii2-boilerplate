<?php

namespace api\controllers;

use api\modules\v1\JwtIdentity;
use api\modules\v1\models\User;
use \backend\modules\auth\models\Users;
use Yii;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

trait AuthTrait
{
    use AuthorizesUser, FormatsToken;

    protected $tokenParam = 'token';

    protected $headerName = 'Authorization';

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    [
                        'class' => QueryParamAuth::class,
                        'tokenParam' => $this->tokenParam
                    ],
                    [
                        'class' => HeaderAuth::class,
                        'headerName' => $this->headerName
                    ],
                ],
                'only' => $this->getAuthenticatedActions(),
                'except' => array_merge($this->getUnAuthenticatedActions(), ['OPTIONS']),
            ],
        ]);
    }

    /**
     * Authenticated actions. By default, all main API endpoints will require authentication
     *
     * @return array
     */
    protected function getAuthenticatedActions()
    {
        return ['index', 'create', 'update', 'view', 'delete'];
    }

    /**
     * Helper to get JWT and corresponding user from GET request or headers
     * @return JwtIdentity
     * @throws ForbiddenHttpException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function getAuthToken()
    {
        // from request
        $token = Yii::$app->request->get($this->tokenParam);
        if ($token === null) {
            // from headers
            $token = Yii::$app->request->getHeaders()->get($this->headerName);

            if ($token === null) {
                throw new ForbiddenHttpException('A token is required to access this resource.');
            }
        }

        $token = $this->formatToken($token);

        $identity = Users::findIdentityByAccessToken($token);

        return new JwtIdentity($token, $identity);
    }

    /**
     * Unauthenticated actions
     *
     * @return array
     */
    protected function getUnAuthenticatedActions()
    {
        return [];
    }
}
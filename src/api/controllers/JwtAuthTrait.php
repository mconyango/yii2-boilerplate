<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-10-22
 * Time: 9:03 PM
 */

namespace api\controllers;


use api\modules\v1\JwtIdentity;
use api\modules\v1\models\User;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\web\ForbiddenHttpException;

trait JwtAuthTrait
{
    use FormatsToken;

    protected $tokenParam = 'token';

    protected $headerName = 'Authorization';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'optional' => array_merge($this->getUnAuthenticatedActions(), ['OPTIONS']),
        ];

        return $behaviors;
    }

    /**
     * Authenticated actions. By default, all main API endpoints will require authentication
     *
     * @return array
     */
    protected function getAuthenticatedActions()
    {
        return null;
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

    /**
     * Helper to get JWT and corresponding user from GET request or headers
     * @return JwtIdentity
     * @throws ForbiddenHttpException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function getAuthToken()
    {
        // from request
        $token = \Yii::$app->request->get($this->tokenParam);
        if ($token === null) {
            // from headers
            $token = \Yii::$app->request->getHeaders()->get($this->headerName);

            if ($token === null) {
                throw new ForbiddenHttpException('A token is required to access this resource.');
            }
        }

        $token = $this->formatToken($token);
        $jwtToken = \Yii::$app->jwt->loadToken($token);

        $identity = User::findIdentityByAccessToken($jwtToken);

        return new JwtIdentity($token, $identity);
    }
}
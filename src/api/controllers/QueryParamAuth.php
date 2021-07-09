<?php
/**
 * Created by PhpStorm.
 * User: antony
 * Date: 5/8/17
 * Time: 12:35 PM
 */

namespace api\controllers;

use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class QueryParamAuth extends AuthMethod
{
    use FormatsToken;

    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'token';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        $accessToken = $request->get($this->tokenParam);
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($this->formatToken($accessToken), get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('An access token is required to access this resource.');
    }
}
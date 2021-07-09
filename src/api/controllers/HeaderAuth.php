<?php
/**
 * Created by PhpStorm.
 * User: antony
 * Date: 9/29/16
 * Time: 10:10 AM
 */

namespace api\controllers;


use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class HeaderAuth extends AuthMethod
{
    use FormatsToken;

    public $headerName = 'Authorization';

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        // get token from header. For satellizer and the rest
        $accessToken = $request->getHeaders()->get($this->headerName);
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
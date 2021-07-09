<?php

namespace api\modules\v1;

use backend\modules\auth\models\UserIdentity;

class JwtIdentity
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var UserIdentity
     */
    public $identity;

    /**
     * JwtIdentity constructor.
     * @param $token
     * @param $identity
     */
    public function __construct($token, $identity)
    {
        $this->token = $token;
        $this->identity = $identity;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return UserIdentity
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param UserIdentity $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }
}
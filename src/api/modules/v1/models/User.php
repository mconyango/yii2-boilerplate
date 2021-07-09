<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-10-23
 * Time: 12:32 AM
 */

namespace api\modules\v1\models;


use api\controllers\JwtHelper;
use sizeg\jwt\Jwt;
use Yii;

class User extends \backend\modules\auth\models\Users
{
    /**
     * {@inheritdoc}
     * @param \Lcobucci\JWT\Token $token
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $userId = (string)$token->getClaim('uid');
        return static::findOne($userId);
    }

    /**
     * @return string
     */
    public function getToken()
    {

        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();
        $hostInfo = Yii::$app->request->hostInfo;
        $builder = $jwt->getBuilder();
        $extraPayload = (array)$this->getExtraPayload();
        foreach ($extraPayload as $k => $v) {
            $builder->withClaim($k, $v);
        }
        $token = $builder->issuedBy($hostInfo)// Configures the issuer (iss claim)
        ->permittedFor($hostInfo)// Configures the audience (aud claim)
        ->identifiedBy(JwtHelper::getJwtId(), true)// Configures the id (jti claim), replicating as a header item
        ->issuedAt($time)// Configures the time that the token was issue (iat claim)
        ->expiresAt($time + (int)JwtHelper::getJwtExpire())// Configures the expiration time of the token (exp claim)
        ->withClaim('uid', $this->id)// Configures a new claim, called "uid"
        ->getToken($signer, $key); // Retrieves the generated token

        return (string)$token;
    }

    /**
     * @return array
     */
    protected function getExtraPayload()
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
        ];
    }
}
<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-10-23
 * Time: 1:16 AM
 */

namespace api\controllers;


use Yii;

class JwtHelper
{
    /**
     * Getter for exp that's used for generation of JWT
     * @return string secret key used to generate JWT
     */
    public static function getJwtExpire()
    {
        return Yii::$app->params['JWT_EXPIRE'] ?? 86400;
    }

    /**
     * Getter for secret key that's used for generation of JWT
     * @return string secret key used to generate JWT
     */
    public static function getJwtId()
    {
        return Yii::$app->params['JWT_ID'] ?? '4f1g23a12aa';
    }
}
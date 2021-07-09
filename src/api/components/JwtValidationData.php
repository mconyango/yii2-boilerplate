<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-10-22
 * Time: 9:45 PM
 */

namespace api\components;


use api\controllers\JwtHelper;
use Yii;
use yii\web\Request;

class JwtValidationData extends \sizeg\jwt\JwtValidationData
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $request = Yii::$app->request;
        $hostInfo = '';
        if ($request instanceof Request) {
            $hostInfo = $request->hostInfo;
        }
        $this->validationData->setIssuer($hostInfo);
        $this->validationData->setAudience($hostInfo);
        $this->validationData->setId(JwtHelper::getJwtId());

        parent::init();
    }
}
<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-10 15:38
 * Time: 15:38
 */

namespace backend\modules\conf\settings;


use common\models\Model;
use Yii;

abstract class BaseSettings extends Model
{
    /**
     * @return \yii2mod\settings\components\Settings
     */
    public static function getSettingsComponent()
    {
        return Yii::$app->settings;
    }
}
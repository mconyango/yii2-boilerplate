<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-11-26 22:48
 * Time: 22:48
 */

namespace console\models\fakers\models;


use common\models\ActiveRecord;
use Yii;

trait FakerModelTrait
{
    /**
     * @param string $propertyName
     * @param string|ActiveRecord $modelClass
     * @param string $idField
     * @return mixed
     * @throws \yii\base\ExitException
     */
    public function getFakerForeignKeyId($propertyName, $modelClass, $idField = 'id')
    {
        if (is_null($this->{$propertyName})) {
            $this->{$propertyName} = $modelClass::getColumnData($idField);
        }

        if (empty($this->{$propertyName})) {
            Yii::$app->controller->stdout($modelClass::getCleanTableName() . " is empty.\n");
            Yii::$app->end();
        }

        $k = array_rand($this->{$propertyName});
        return $this->{$propertyName}[$k];
    }
}
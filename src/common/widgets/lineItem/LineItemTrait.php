<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-05-28
 * Time: 19:41
 */

namespace common\widgets\lineItem;


use Yii;

trait LineItemTrait
{
    /**
     * @param int $count
     * @param array $config
     * @param mixed $condition
     * @param array $params
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function getLineModels($count, $config = [], $condition = null, $params = [])
    {
        $models = [];
        if (!empty($condition)) {
            $models = static::find()->andWhere($condition, $params)->all();
            if (!empty($models)) {
                return $models;
            }
        }
        /* @var $baseModel $this */
        $config['class'] = static::class;
        $baseModel = Yii::createObject($config);

        for ($i = 0; $i < $count; $i++) {
            $model = clone $baseModel;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Whether the model should be validated
     * @return boolean
     */
    public function shouldValidate()
    {
        return true;
    }

    /**
     * Whether the model should be saved
     * @return boolean
     */
    public function shouldSave()
    {
        return true;
    }
}
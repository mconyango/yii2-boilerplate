<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/05
 * Time: 2:03 PM
 */

namespace common\models;


use Yii;
use yii\helpers\ArrayHelper;

class Model extends \yii\base\Model
{
    /**
     * Get short classname (without the namespace)
     * @return string
     * @throws \ReflectionException
     */
    public static function shortClassName()
    {
        $reflect = new \ReflectionClass(static::class);
        return $reflect->getShortName();
    }

    /**
     * Creates and populates a set of models.
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @param string $index
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function createMultiple($modelClass, $multipleModels = [], $index = 'id')
    {
        /* @var $model ActiveRecord */
        /* @var $newModel ActiveRecord */
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, $index, $index));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item[$index]) && !empty($item[$index]) && isset($multipleModels[$item[$index]])) {
                    $models[] = $multipleModels[$item[$index]];
                } else {
                    $newModel = new $modelClass;
                    if (is_array($item) && !empty($item)) {
                        $newModel->attributes = $item;
                    }
                    $models[] = $newModel;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }
}
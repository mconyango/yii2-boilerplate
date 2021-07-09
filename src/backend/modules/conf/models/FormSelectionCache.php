<?php

namespace backend\modules\conf\models;

use common\models\ActiveRecord;
use common\models\Model;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sys_cache_form_selection".
 *
 * @property integer $id
 * @property string $route
 * @property integer $user_id
 * @property string $form_class
 * @property string $value
 * @property string $timestamp
 */
class FormSelectionCache extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_cache_form_selection}}';
    }

    /**
     * @return int
     */
    public static function clearCache()
    {
        $expiry = 15;//mins
        $condition = '([[timestamp]] < DATE_SUB(NOW(), INTERVAL :expiry MINUTE))';
        $params = [':expiry' => $expiry];
        return static::deleteAll($condition, $params);
    }

    /**
     * @param Model|ActiveRecord $formModel
     * @param array $fields array of select fields (fields with drop down list to be cached)
     * @return bool
     */
    public static function setCache($formModel, $fields = [])
    {
        static::clearCache();

        $route = Yii::$app->controller->getRoute();
        $user_id = Yii::$app->user->id;
        $form_class = get_class($formModel);
        $value = [];
        foreach ($fields as $field) {
            $value[$field] = $formModel->{$field};
        }

        $cacheModel = static::find()->andWhere(['route' => $route, 'user_id' => $user_id, 'form_class' => $form_class])->one();
        if (null === $cacheModel) {
            $cacheModel = new FormSelectionCache([
                'route' => $route,
                'user_id' => $user_id,
                'form_class' => $form_class,
            ]);
        }

        $cacheModel->value = @serialize($value);
        $cacheModel->timestamp = new Expression('NOW()');
        return $cacheModel->save(false);
    }

    /**
     * @param Model|ActiveRecord $formModel
     * @return ActiveRecord|Model
     */
    public static function getCache($formModel)
    {
        $route = Yii::$app->controller->getRoute();
        $user_id = Yii::$app->user->id;
        $form_class = get_class($formModel);

        $condition = '[[route]]=:route AND [[user_id]]=:user_id AND [[form_class]]=:form_class';
        $params = [':route' => $route, ':user_id' => $user_id, ':form_class' => $form_class];

        $cachedData = static::getScalar('value', $condition, $params);
        if (!empty($cachedData)) {
            $cachedData = @unserialize($cachedData);

            foreach ($cachedData as $field => $value) {
                if ($formModel instanceof ActiveRecord && $formModel->hasAttribute($field)) {
                    $formModel->{$field} = $value;
                } elseif ($formModel->hasProperty($field)) {
                    $formModel->{$field} = $value;
                }
            }
        }

        static::clearCache();

        return $formModel;
    }
}

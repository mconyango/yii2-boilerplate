<?php

namespace backend\models;


use common\models\ActiveRecord;
use yii\base\Exception;
use yii\di\Instance;
use yii\helpers\Json;

/**
 * This is the model class for table "sys_form_draft".
 *
 * @property int $id
 * @property string $model_attributes
 * @property string $model_class
 * @property string $created_at
 * @property int $created_by
 */
class FormDraft extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sys_form_draft}}';
    }


    /**
     * @param ActiveRecord|ActiveRecord[] $activeRecord
     * @param null|integer $id
     * @return FormDraft
     */
    public static function saveModel($activeRecord, $id = null)
    {
        $model = static::findOne(['id' => $id]);
        if (null === $model) {
            $model = new static([]);
            $model->model_class = get_class(is_array($activeRecord) ? $activeRecord[0] : $activeRecord);
        }

        $model->model_attributes = self::getModelAttributes($activeRecord);
        $model->save(false);
        return $model;
    }

    /**
     * @param ActiveRecord|ActiveRecord[] $activeRecord
     * @return string
     */
    private static function getModelAttributes($activeRecord)
    {
        $activeRecords = [];
        if (!is_array($activeRecord)) {
            $activeRecords[] = $activeRecord;
        } else {
            $activeRecords = $activeRecord;
        }
        unset($activeRecord);
        $attributes = [];
        foreach ($activeRecords as $model) {
            $columns = $model->attributes;
            foreach ($model->safeAttributes() as $property) {
                if (!isset($columns[$property])) {
                    $columns[$property] = $model->{$property};
                }
            }
            $attributes[] = $columns;
        }

        return Json::encode($attributes);
    }

    /**
     * @return ActiveRecord|ActiveRecord[]
     */
    public function getModel()
    {
        $attributes = Json::decode($this->model_attributes);
        /* @var $model ActiveRecord */
        $model = new $this->model_class();
        $models = [];
        foreach ($attributes as $index => $columns) {
            $newModel = clone $model;
            foreach ($columns as $k => $v) {
                if (($model->hasAttribute($k) || property_exists($model, $k)) && $model->isAttributeSafe($k)) {
                    if ($k === 'is_active') {
                        $newModel->{$k} = 1;
                    } else {
                        $newModel->{$k} = $v;
                    }
                }
            }
            $models[$index] = $newModel;
        }
        if (count($models) === 1) {
            return $models[0];
        }
        return $models;
    }

    /**
     * @param integer $id
     * @param string $modelClass
     * @return mixed|null|ActiveRecord
     */
    public static function getSavedDraft($id, $modelClass)
    {
        $model = null;
        if ($id) {
            $draft = static::findOne($id);
            if (null !== $draft) {
                $model = $draft->getModel();
            }
        }
        try {
            if (is_array($model)) {
                Instance::ensure($model[0], $modelClass);
            } else {
                Instance::ensure($model, $modelClass);
            }
        } catch (Exception $e) {
            $model = null;
        }
        return $model;
    }

    /**
     * @param string $id
     * @return int
     */
    public static function deleteDraft($id)
    {
        return static::deleteAll(['id' => $id]);
    }
}

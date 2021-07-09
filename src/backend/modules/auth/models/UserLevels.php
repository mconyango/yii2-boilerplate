<?php

namespace backend\modules\auth\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;

/**
 * This is the model class for table "auth_user_levels".
 *
 * @property integer $id
 * @property string $name
 * @property string $forbidden_items
 * @property integer $parent_id
 * @property integer $is_active
 */
class UserLevels extends ActiveRecord implements ActiveSearchInterface
{

    use ActiveSearchTrait;

    const LEVEL_DEV = -1;
    const LEVEL_SUPER_ADMIN = 1;
    const LEVEL_ADMIN = 2;
    const LEVEL_ORGANIZATION = 3;
    const LEVEL_ORGANIZATION_CLIENT = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_user_levels}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['id', 'parent_id', 'is_active'], 'integer'],
            [['name'], 'string', 'max' => 60],
            [['id', 'name'], 'unique'],
            [['forbidden_items'], 'safe'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'name' => Lang::t('Name'),
            'forbidden_items' => Lang::t('Forbidden Items'),
            'parent_id' => Lang::t('Parent'),
            'is_active' => Lang::t('Active'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function searchParams()
    {
        return [
            ['id', 'id'],
            ['name', 'name'],
            'is_active',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->id === self::LEVEL_DEV) {
            $this->parent_id = NULL;
            $this->forbidden_items = NULL;
        }
        if (!empty($this->forbidden_items))
            $this->forbidden_items = @serialize($this->forbidden_items);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        if (!empty($this->forbidden_items)) {
            $this->forbidden_items = @unserialize($this->forbidden_items);
        }

        parent::afterFind();
    }

    /**
     * get the forbidden resources for a given user type
     * @param string $id
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getForbiddenResources($id)
    {
        $model = static::loadModel($id);
        $forbidden_items = (array)$model->forbidden_items;
        if (!empty($model->parent_id)) {
            $forbidden_items = array_merge($forbidden_items, static::getForbiddenResources($model->parent_id));
        }

        return $forbidden_items;
    }


}

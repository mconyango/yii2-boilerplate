<?php

namespace backend\modules\auth\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;

/**
 * This is the model class for table "auth_resources".
 *
 * @property string $id
 * @property string $name
 * @property integer $viewable
 * @property integer $creatable
 * @property integer $editable
 * @property integer $deletable
 * @property integer $executable
 */
class Resources extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * Initializes the object.
     * This method is called at the end of the constructor.
     * The default implementation will trigger an [[EVENT_INIT]] event.
     * If you override this method, make sure you call the parent implementation at the end
     * to ensure triggering of the event.
     */
    public function init()
    {
        if ($this->viewable === null)
            $this->viewable = true;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_resources}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['viewable', 'creatable', 'editable', 'deletable', 'executable'], 'integer'],
            [['id'], 'string', 'max' => 30],
            ['id', 'unique'],
            [['name'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('Key'),
            'name' => Lang::t('Name'),
            'viewable' => Lang::t('Viewable'),
            'creatable' => Lang::t('Creatable'),
            'editable' => Lang::t('Editable'),
            'deletable' => Lang::t('Deletable'),
            'executable' => Lang::t('Executable'),
        ];
    }

    /**
     * Get items
     * @param array $excludedItems Items not to be included
     * @return array $items
     */
    public static function getResources($excludedItems = null)
    {
        $condition = [];
        $params = [];
        if (!empty($excludedItems)) {
            $condition = ['NOT IN', 'id', $excludedItems];
        }
        return static::getData('*', $condition, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            ['id', 'id'],
            ['name', 'name'],
        ];
    }
}

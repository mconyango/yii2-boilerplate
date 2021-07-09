<?php

namespace backend\modules\conf\models;

use common\models\ActiveRecord;
use Yii;

/**
 * This is the model class for table "conf_timezone_ref".
 *
 * @property integer $id
 * @property string $name
 */
class Timezone extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_timezone_ref}}';
    }

    /**
     * composes drop-down list data from a model using Html::listData function
     * @see CHtml::listData();
     * @param string $valueColumn
     * @param string $textColumn
     * @param boolean $prompt
     * @param string $condition
     * @param array $params
     * @param array $options
     *
     *  <pre>
     *   array(
     *    "orderBy"=>""//String,
     *    "groupField"=>null//String could be anonymous function that gets the group field
     *    "extraColumns"=>[]// array : you must pass at least the grouping field if groupField is an anonymous function
     * )
     * </pre>
     *
     * @return array
     */
    public static function getListData($valueColumn = 'name', $textColumn = 'name', $prompt = false, $condition = '', $params = [], $options = [])
    {
        return parent::getListData($valueColumn, $textColumn, $prompt, $condition, $params, $options);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique', 'message' => '{value} already exists.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }


}

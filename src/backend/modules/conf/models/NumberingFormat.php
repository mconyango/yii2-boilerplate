<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "core_master_numbering_format".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property integer $next_number
 * @property integer $min_digits
 * @property string $prefix
 * @property string $suffix
 * @property string $preview
 * @property int $is_private
 * @property int $is_active
 * @property string $created_at
 * @property integer $created_by
 */
class NumberingFormat extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_null($this->min_digits))
            $this->min_digits = 3;
        if (is_null($this->next_number))
            $this->next_number = 1;
        parent::init();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%core_master_numbering_format}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['next_number', 'min_digits', 'is_private', 'is_active'], 'integer'],
            [['code'], 'string', 'max' => 60],
            [['name'], 'string', 'max' => 255],
            [['prefix', 'suffix'], 'string', 'max' => 5],
            [['preview'], 'string', 'max' => 128],
            [['code'], 'unique', 'message' => Lang::t('{attribute} {value} has been defined')],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('#'),
            'code' => Lang::t('Code'),
            'name' => Lang::t('Name'),
            'description' => Lang::t('Description'),
            'next_number' => Lang::t('Next Number'),
            'min_digits' => Lang::t('Minimum Digits'),
            'prefix' => Lang::t('Prefix'),
            'suffix' => Lang::t('Suffix'),
            'preview' => Lang::t('Preview'),
            'is_private' => Lang::t('Private'),
            'is_active' => Lang::t('Active'),
        ];
    }

    /**
     * Get next formatted number
     * @param string $code
     * @param boolean $increment_next_number
     * @return string $formatted_number
     * @throws \yii\db\Exception
     */
    public static function getNextFormattedNumber($code, $increment_next_number = true)
    {
        $condition = '[[code]]=:code';
        $params = [':code' => $code];
        $format = static::getOneRow('*', $condition, $params);
        $next_number = ArrayHelper::getValue($format, 'next_number', 1);
        $min_digits = ArrayHelper::getValue($format, 'min_digits', 3);
        $prefix = ArrayHelper::getValue($format, 'prefix', '');
        $suffix = ArrayHelper::getValue($format, 'suffix', '');
        $template = '{{prefix}}{{number}}{{suffix}}';

        $number = str_pad($next_number, $min_digits, "0", STR_PAD_LEFT);
        if (!empty($format) && $increment_next_number) {
            $next_number++;
            Yii::$app->db->createCommand()
                ->update(static::tableName(), ['next_number' => $next_number], $condition, $params)
                ->execute();
        }
        return strtr($template, [
            '{{prefix}}' => $prefix,
            '{{number}}' => $number,
            '{{suffix}}' => $suffix,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function searchParams()
    {
        return [
            ['code', 'code'],
            ['name', 'name'],
            'is_active',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
    }
}

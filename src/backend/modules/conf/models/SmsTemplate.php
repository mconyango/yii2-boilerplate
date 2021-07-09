<?php

namespace backend\modules\conf\models;

use backend\modules\core\models\Organization;
use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;

/**
 * This is the model class for table "sms_template".
 *
 * @property int $id
 * @property int $org_id
 * @property string $code
 * @property string $name
 * @property string $template
 * @property string $available_placeholders
 * @property string $created_at
 * @property int $created_by
 *
 * @property Organization $org
 */
class SmsTemplate extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sms_template}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'template'], 'required'],
            [['code'], 'string', 'max' => 128],
            [['org_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['template', 'available_placeholders'], 'string', 'max' => 1000],
            ['code', 'unique', 'targetAttribute' => ['org_id', 'code'], 'message' => 'You already have copy of this template for your organization.Please Update it'],
            //[[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('ID'),
            'org_id' => Lang::t('Organization'),
            'code' => Lang::t('Code'),
            'name' => Lang::t('Name'),
            'template' => Lang::t('Template'),
            'available_placeholders' => Lang::t('Available Placeholders'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            ['code', 'code'],
            ['name', 'name'],
            'org_id',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrg()
    {
        return $this->hasOne(Organization::class, ['id' => 'org_id']);
    }
}

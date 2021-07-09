<?php

namespace backend\modules\auth\models;

use common\models\ActiveRecord;

/**
 * This is the model class for table "auth_permission".
 *
 * @property integer $id
 * @property integer $role_id
 * @property string $resource_id
 * @property integer $can_view
 * @property integer $can_create
 * @property integer $can_update
 * @property integer $can_delete
 * @property integer $can_execute
 *
 * @property Resources $resource
 * @property Roles $role
 */
class Permission extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_permission}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role_id', 'resource_id'], 'required'],
            [['role_id', 'can_view', 'can_create', 'can_update', 'can_delete',], 'integer'],
            [['resource_id'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role',
            'resource_id' => 'Resource',
            'can_view' => 'Can View',
            'can_create' => 'Can Create',
            'can_update' => 'Can Update',
            'can_delete' => 'Can Delete',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resources::class, ['id' => 'resource_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Roles::class, ['id' => 'role_id']);
    }
}

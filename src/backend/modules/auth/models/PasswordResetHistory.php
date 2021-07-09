<?php

namespace app\modules\auth\models;

//use Yii;
use backend\modules\auth\models\Users;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;

/**
 * This is the model class for table "auth_password_reset_history".
 *
 * @property int $id
 * @property int $user_id
 * @property string $old_password_hash
 * @property string $new_password_hash
 * @property string $ip_address
 * @property string $password_reset_token
 * @property string $created_at
 * @property int $created_by
 */
class PasswordResetHistory extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auth_password_reset_history}}';
    }

    /**
     * Loads user password hash, token, etc. before change of password
     * @param Users $user
     * @param integer $creator_id id of user making the change. Set to NULL if admin
     */
    public function loadOldData($user, $creator_id = null)
    {
        $this->old_password_hash = $user->password_hash;
        $this->password_reset_token = $user->password_reset_token;
        $this->user_id = $user->id;
        $this->created_by = $creator_id;
    }

    /**
     * Logs password changes made
     * @param Users $user user model
     */
    public function logPasswordReset($user)
    {
        $this->new_password_hash = $user->password_hash;
        $this->save(false);
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'old_password_hash', 'new_password_hash'], 'required'],
            [['user_id', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['old_password_hash', 'new_password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'old_password_hash' => 'Old Password Hash',
            'new_password_hash' => 'New Password Hash',
            'ip_address' => 'Ip Address',
            'password_reset_token' => 'Password Reset Token',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchParams()
    {
        return [
            'id',
            'user_id',
        ];
    }
}

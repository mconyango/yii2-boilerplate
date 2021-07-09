<?php

namespace backend\modules\auth\forms;

use backend\modules\auth\models\Users;
use common\helpers\Lang;
use common\models\Model;
use yii\base\InvalidArgumentException;

/**
 * Password reset form.
 */
class ResetPasswordForm extends Model
{
    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $confirm;

    /**
     * @var string
     */
    public $username;

    /**
     * @var Users
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param string $token Password reset token.
     * @param array $config Name-value pairs that will be used to initialize the object properties.
     *
     * @throws \yii\base\InvalidParamException  If token is empty or not valid.
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }

        $this->_user = Users::findByPasswordResetToken($token);

        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }

        parent::__construct($config);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            [['confirm'], 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
            // use passwordStrengthRule() method to determine password strength
            [['username'], 'safe'],
            Users::passwordValidator(),
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'password' => Lang::t('Password'),
            'confirm' => Lang::t('Repeat Password')
        ];
    }

    /**
     * Resets password.
     *
     * @return bool Whether the password was reset.
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPasswordHash($this->password);
        $user->removePasswordResetToken();

        return $user->save();
    }

    /**
     * @return Users
     */
    public function getUser()
    {
        return $this->_user;
    }
}

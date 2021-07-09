<?php
namespace backend\modules\auth\forms;

use common\helpers\Lang;
use backend\modules\auth\models\Users;
use common\models\Model;
use Yii;
use yii\db\Expression;
use yii2tech\authlog\AuthLogLoginFormBehavior;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $rememberMe = false;
    public $verifyCode;

    /**
     * @var \backend\modules\auth\models\Users
     */
    private $_user = false;

    public function behaviors()
    {
        return [
            'authLog' => [
                'class' => AuthLogLoginFormBehavior::class,
                'findIdentity' => 'findIdentity',
                'verifyRobotFailedLoginSequence' => 20,
                'verifyRobotAttribute' => 'verifyCode',
                'verifyRobotRule' => ['captcha', 'captchaAction' => '/auth/auth/captcha'],
                'deactivateFailedLoginSequence' => 50,
                //'deactivateIdentity' => function ($identity) {return false;//return Users::updateAll(['status' => Users::STATUS_BLOCKED], ['id' => $identity->attributes['id']]);},
            ],
        ];
    }

    public function findIdentity()
    {
        return Users::findByUsername($this->username);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],
            // username and password are required on default scenario
            [['username', 'password'], 'required', 'on' => 'default'],
            // email and password are required on 'lwe' (login with email) scenario
            [['email', 'password'], 'required', 'on' => 'lwe'],
            ['verifyCode', 'safe'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute The attribute currently being validated.
     * @param array $params The additional name-value pairs.
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                // if scenario is 'lwe' we use email, otherwise we use username
                $field = ($this->scenario === 'lwe') ? 'email' : 'username';

                $this->addError($attribute, 'Incorrect ' . $field . ' or password.');
            }
        }
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'username' => Lang::t('Username'),
            'password' => Lang::t('Password'),
            'email' => Lang::t('Email'),
            'rememberMe' => Lang::t('Remember me'),
        ];
    }

    /**
     * Logs in a user using the provided username|email and password.
     *
     * @return bool Whether the user is logged in successfully.
     */
    public function login()
    {
        if ($this->validate()) {
            $success = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);

            if ($success) {
                //set timezone
                if (!empty(Yii::$app->user->identity->timezone)) {
                    Yii::$app->localTime->setTimeZone(Yii::$app->user->identity->timezone);
                }
                //update last login date and time
                Users::updateAll(['last_login' => new Expression('NOW()')], ['id' => Yii::$app->user->id]);
            }

            return $success;
        } else {
            return false;
        }
    }

    /**
     * Finds user by username or email in 'lwe' scenario.
     *
     * @return Users|null|static
     */
    public function getUser()
    {
        if ($this->_user === false) {
            // in 'lwe' scenario we find user by email, otherwise by username
            if ($this->scenario === 'lwe') {
                $this->_user = Users::findByEmail($this->email);
            } else {
                $this->_user = Users::findByUsername($this->username);
            }
        }

        return $this->_user;
    }

    /**
     * Checks to see if the given user has NOT activated his account yet.
     * We first check if user exists in our system,
     * and then did he activated his account.
     *
     * @return bool True if not activated.
     */
    public function notActivated()
    {
        // if scenario is 'lwe' we will use email as our username, otherwise we use username
        $username = ($this->scenario === 'lwe') ? $this->email : $this->username;

        if ($user = Users::userExists($username, $this->password, $this->scenario)) {
            if ($user->status === Users::STATUS_NOT_ACTIVE) {
                return true;
            } else {
                return false;
            }
        }
    }
}

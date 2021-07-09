<?php

namespace backend\modules\auth\models;

use app\modules\auth\models\PasswordResetHistory;
use backend\modules\conf\settings\PasswordSettings;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use kartik\password\StrengthValidator;
use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii2tech\authlog\AuthLogIdentityBehavior;

/**
 * UserIdentity class for "user" table.
 * This is a base user class that is implementing IdentityInterface.
 * User model should extend from this model, and other user related models should
 * extend from User model.
 *
 * @property integer $id
 * @property string $name
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $account_activation_token
 * @property string $auth_key
 * @property integer $status
 * @property string $phone
 * @property string $timezone
 * @property integer $level_id
 * @property integer $is_main_account
 * @property integer $role_id
 * @property string $profile_image
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $last_login
 * @property integer $require_password_change
 * @property integer $auto_generate_password
 *
 * @property Roles $role
 * @property UserLevels $level
 */
abstract class UserIdentity extends ActiveRecord implements IdentityInterface
{
    //status constants
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 2;
    const STATUS_BLOCKED = 3;
    //scenario constants
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CHANGE_PASSWORD = 'changePassword';
    const SCENARIO_RESET_PASSWORD = 'resetPassword';
    const SCENARIO_SIGNUP = 'signup';
    /**
     * @var string
     */
    public $currentPassword;

    /**
     * @var string
     */
    public $password;

    /**
     * confirm password
     * @var string
     */
    public $confirm;

    /**
     * Declares the name of the database table associated with this AR class.
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%auth_users}}';
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param  int|string $id The user id.
     * @return IdentityInterface|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given access token.
     *
     * @param  mixed $token
     * @param  null $type
     * @return void|IdentityInterface
     *
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     *
     * @return int|mixed|string
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given
     * identity ID. The key should be unique for each individual user, and
     * should be persistent so that it can be used to check the validity of
     * the user identity. The space of such keys should be big enough to defeat
     * potential identity attacks.
     *
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * @param  string $authKey The given auth key.
     * @return boolean          Whether the given auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Validates password.
     *
     * @param  string $password
     * @return bool
     *
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Get the user's name
     *
     * @return string
     */
    public function getUserName()
    {

        return $this->username;
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param  string $password
     *
     * @throws \yii\base\Exception
     */
    public function setPasswordHash($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function behaviors()
    {
        return [
            'authLog' => [
                'class' => AuthLogIdentityBehavior::class,
                'authLogRelation' => 'authLogs',
                'defaultAuthLogData' => function ($model) {
                    return [
                        'ip' => Yii::$app->request->getUserIP(),
                        'host' => @gethostbyaddr(Yii::$app->request->getUserIP()),
                        'url' => Yii::$app->request->getAbsoluteUrl(),
                        'userAgent' => Yii::$app->request->getUserAgent(),
                    ];
                },
            ],
        ];
    }

    public function getAuthLogs()
    {
        return $this->hasMany(AuthLog::class, ['userId' => 'id']);
    }

    /**
     * Finds user by username.
     *
     * @param  string $username
     * @return $this
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by email.
     *
     * @param  string $email
     * @return $this
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token.
     *
     * @param  string $token Password reset token.
     * @return $this
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by account activation token.
     *
     * @param  string $token Account activation token.
     * @return $this
     * @throws \yii\web\NotFoundHttpException
     */
    public static function findByAccountActivationToken($token)
    {
        return static::loadModel(['account_activation_token' => $token, 'status' => self::STATUS_NOT_ACTIVE,], false);
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param  string $token Password reset token.
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }


    public function autoGeneratePassword()
    {
        $length = 10;
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        shuffle($chars);
        $password = implode(array_slice($chars, 0, $length));
        $this->password = $password;
        $this->confirm = $password;
    }

    /**
     * Checks to see if the given user exists in our database.
     * If LoginForm scenario is set to lwe (login with email), we need to check
     * user's email and password combo, otherwise we check username/password.
     * NOTE: used in LoginForm model.
     *
     * @param  string $username Can be either username or email based on scenario.
     * @param  string $password
     * @param  string $scenario
     * @return bool|static
     */
    public static function userExists($username, $password, $scenario)
    {
        // if scenario is 'lwe', we need to check email, otherwise we check username
        $field = ($scenario === 'lwe') ? 'email' : 'username';

        if ($user = static::findOne([$field => $username])) {
            if ($user->validatePassword($password)) {
                return $user;
            } else {
                return false; // invalid password
            }
        } else {
            return false; // invalid username|email
        }
    }

    /**
     * @param $status
     * @return string
     */
    public static function decodeStatus($status)
    {
        $decoded = $status;
        switch ($status) {
            case self::STATUS_ACTIVE:
                $decoded = Lang::t('Active');
                break;
            case self::STATUS_NOT_ACTIVE:
                $decoded = Lang::t('Pending Activation');
                break;
            case self::STATUS_BLOCKED:
                $decoded = Lang::t('Blocked');
                break;
        }

        return $decoded;
    }

    /**
     * Status options that can be used in dropdown list
     * @param mixed $tip
     * @return array
     */
    public static function statusOptions($tip = false)
    {
        return Utils::appendDropDownListPrompt([
            self::STATUS_ACTIVE => static::decodeStatus(self::STATUS_ACTIVE),
            self::STATUS_NOT_ACTIVE => static::decodeStatus(self::STATUS_NOT_ACTIVE),
            self::STATUS_BLOCKED => static::decodeStatus(self::STATUS_BLOCKED),
        ], $tip);
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Generates new account activation token.
     */
    public function generateAccountActivationToken()
    {
        $this->account_activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes account activation token.
     */
    public function removeAccountActivationToken()
    {
        $this->account_activation_token = null;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->generateAuthKey();
        }

        if ($this->isNewRecord || $this->scenario === self::SCENARIO_CHANGE_PASSWORD || $this->scenario === self::SCENARIO_RESET_PASSWORD) {
            if ($this->auto_generate_password) {
                $this->autoGeneratePassword();
            }
            $this->setPasswordHash($this->password);
        }

        if ($this->scenario === self::SCENARIO_SIGNUP) {
            $this->generateAccountActivationToken();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Roles::class, ['id' => 'role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLevel()
    {
        return $this->hasOne(UserLevels::class, ['id' => 'level_id']);
    }

    public static function passwordValidator()
    {
        $preset = PasswordSettings::getPreset();
        $usePreset = PasswordSettings::getUsePreset();

        $options = [
            ['password'],
            StrengthValidator::class,
            'userAttribute' => 'username'
        ];

        if ($usePreset) {
            $options['preset'] = $preset;
        } else {
            $options['min'] = PasswordSettings::getMinLength();
            $options['max'] = PasswordSettings::getMaxLength();
            $options['lower'] = PasswordSettings::getMinLower();
            $options['upper'] = PasswordSettings::getMinUpper();
            $options['digit'] = PasswordSettings::getMinDigit();
            $options['special'] = PasswordSettings::getMinSpecial();
        }
        return $options;
    }

    /**
     * @return array
     */
    public function passwordHistoryValidator()
    {
        return [
            'password',
            function () {
                $limit = 5;//@todo define in settings
                $passwordHistory = PasswordResetHistory::getColumnData('old_password_hash', ['user_id' => $this->id], [], ['orderBy' => ['id' => SORT_DESC], 'limit' => $limit]);
                foreach ($passwordHistory as $p) {
                    if (Yii::$app->security->validatePassword($this->password, $p)) {
                        $this->addError('password', Lang::t('You cannot use a recent password.'));
                        return false;
                    }
                }
                return true;
            },
            'on' => [self::SCENARIO_CHANGE_PASSWORD, self::SCENARIO_RESET_PASSWORD]
        ];
    }

    public function validateCurrentPassword()
    {
        if (!$this->validatePassword($this->currentPassword)) {
            $this->addError('currentPassword', Lang::t('Current password is not valid.'));
        }
    }

    /**
     * Check whether account belongs to a user
     * @return bool
     */
    public function isMyAccount()
    {
        return ($this->id == Yii::$app->user->id);
    }

    /**
     * @return bool
     */
    public static function isRequirePasswordChange()
    {
        return static::getFieldByPk(Yii::$app->user->id, 'require_password_change');
    }
}
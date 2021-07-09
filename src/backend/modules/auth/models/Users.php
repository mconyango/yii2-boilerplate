<?php

namespace backend\modules\auth\models;


use backend\modules\auth\Session;
use backend\modules\conf\settings\SystemSettings;
use common\helpers\DbUtils;
use common\helpers\FileManager;
use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveSearchTrait;
use common\models\ActiveSearchInterface;
use Yii;
use yii\imagine\Image;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "auth_users".
 *
 * @property integer $branch_id
 *
 */
class Users extends UserIdentity implements ActiveSearchInterface
{
    use ActiveSearchTrait, UserNotificationTrait;

    /**
     *
     * @var bool
     */
    public $send_email = false;
    /**
     * @var
     */
    public $tmp_profile_image;

    const UPLOADS_DIR = 'users';

    public $verifyCode;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->timezone)) {
            $this->timezone = SystemSettings::getDefaultTimezone();
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'username', 'email', 'level_id'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['email', 'email'],
            [['level_id', 'role_id', 'auto_generate_password', 'branch_id', 'require_password_change'], 'integer'],
            [['name', 'profile_image'], 'string', 'max' => 128],
            ['username', 'string', 'min' => 4, 'max' => 30],
            // password field is required on 'create' scenario
            [
                'password',
                'required',
                'on' => [
                    self::SCENARIO_CREATE,
                    self::SCENARIO_CHANGE_PASSWORD,
                    self::SCENARIO_RESET_PASSWORD
                ]
            ],
            [
                ['confirm'],
                'compare',
                'compareAttribute' => 'password',
                'on' => [
                    self::SCENARIO_CHANGE_PASSWORD,
                    self::SCENARIO_CREATE,
                    self::SCENARIO_RESET_PASSWORD,
                    self::SCENARIO_SIGNUP
                ],
                'message' => Lang::t('Passwords do not match.')
            ],
            [['username'], 'unique', 'message' => 'This username has already been taken.'],
            ['email', 'unique', 'message' => 'This Email address has already been taken.'],
            [['timezone'], 'string', 'max' => 60],
            [['send_email', 'tmp_profile_image'], 'safe'],
            [['phone'], 'string', 'max' => 15],
            [['currentPassword'], 'required', 'on' => self::SCENARIO_CHANGE_PASSWORD],
            [
                ['currentPassword'],
                'validateCurrentPassword',
                'skipOnError' => false,
                'on' => self::SCENARIO_CHANGE_PASSWORD
            ],
            [
                ['status', 'username', 'email', 'phone', 'last_login', self::SEARCH_FIELD],
                'safe',
                'on' => self::SCENARIO_SEARCH
            ],
            static::passwordValidator(),
            $this->passwordHistoryValidator(),
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
            'username' => Lang::t('Username'),
            'email' => Lang::t('Email'),
            'phone' => Lang::t('Mobile'),
            'status' => Lang::t('Status'),
            'timezone' => Lang::t('Timezone'),
            'password' => Lang::t('Password'),
            'confirm' => Lang::t('Confirm Password'),
            'currentPassword' => Lang::t('Current Password'),
            'level_id' => Lang::t('Account Type'),
            'role_id' => Lang::t('Role'),
            'profile_image' => Lang::t('Profile Image'),
            'tmp_profile_image' => Lang::t('Profile Image'),
            'created_at' => Lang::t('Created At'),
            'created_by' => Lang::t('Created By'),
            'updated_at' => Lang::t('Updated At'),
            'last_login' => Lang::t('Last Login'),
            'send_email' => Lang::t('Email the login details to the user.'),
            'auto_generate_password' => Lang::t('Auto Generate Password'),
            'branch_id' => Lang::t('Branch'),
            'require_password_change' => Lang::t('Force password change on login')
        ];
    }

    /**
     * @inheritdoc
     */
    public function searchParams()
    {
        return [
            ['email', 'email'],
            ['name', 'name'],
            ['username', 'username'],
            'id',
            'status',
            'level_id',
            'role_id',
            'is_main_account',
            'branch_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->updateProfileImage();
        if ($this->scenario === self::SCENARIO_CREATE) {
            $this->sendLoginDetailsEmail();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function getListData($valueColumn = 'id', $textColumn = 'name', $prompt = true, $condition = ['status' => self::STATUS_ACTIVE], $params = [], $options = [])
    {
        $options['orderBy'] = ['name' => SORT_ASC];
        return parent::getListData($valueColumn, $textColumn, $prompt, $condition, $params, $options);
    }

    /**
     * @param mixed $condition
     * @param array $params
     * @param string $levelIdAttribute
     * @return array
     */
    public static function appendLevelCondition($condition = '', $params = [], $levelIdAttribute = 'level_id')
    {
        if (Yii::$app->user->getIsGuest()) {
            return [$condition, $params];
        }
        $levelIds = UserLevels::getColumnData('id', '[[id]]<:id', [':id' => Session::userLevelId()]);
        if (empty($levelIds)) {
            return [$condition, $params];
        }
        return DbUtils::appendInCondition($levelIdAttribute, $levelIds, $condition, $params, 'NOT IN');
    }

    /**
     * @param bool $throwException
     * @param bool $allowSameLevel
     * @param bool $allowSameRole
     * @param bool $allowMyAccount
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function checkPermission($throwException = false, $allowSameLevel = true, $allowSameRole = false, $allowMyAccount = true)
    {
        $hasPermission = false;
        if ($this->level_id === null) {
            $hasPermission = true;
        } elseif (Session::userLevelId() < $this->level_id) {
            $hasPermission = true;
        } elseif ($allowMyAccount && $this->isMyAccount()) {
            $hasPermission = true;
        } elseif ($allowSameLevel && Session::userLevelId() === $this->level_id) {
            $hasPermission = true;
        } elseif ($allowSameRole && Session::userRoleId() === $this->role_id && Session::userLevelId() === $this->level_id) {
            $hasPermission = true;
        }

        if (!$hasPermission && $throwException) {
            throw new ForbiddenHttpException();
        }
        return $hasPermission;
    }

    /**
     * Get user levels to display in the drop-down list
     * @param mixed $tip
     * @return array
     */
    public static function levelIdListData($tip = false)
    {
        list($condition, $params) = static::appendLevelCondition(null, [], 'id');
        return UserLevels::getListData('id', 'name', $tip, $condition, $params, ['orderBy' => ['id' => SORT_ASC]]);
    }

    //PROFILE IMAGE HANDLERS

    /**
     * Get the dir of a user
     * @return string
     */
    public function getDir()
    {
        return FileManager::createDir(static::getBaseDir() . DIRECTORY_SEPARATOR . $this->id);
    }

    /**
     * @return mixed
     */
    public static function getBaseDir()
    {
        return FileManager::createDir(FileManager::getUploadsDir() . DIRECTORY_SEPARATOR . self::UPLOADS_DIR);
    }

    /**
     * Update profile image
     */
    protected function updateProfileImage()
    {
        if (empty($this->tmp_profile_image))
            return false;
        //using fineuploader
        $ext = FileManager::getFileExtension($this->tmp_profile_image);
        $image_name = Utils::generateSalt() . '.' . $ext;
        $temp_dir = dirname($this->tmp_profile_image);
        $new_path = $this->getDir() . DIRECTORY_SEPARATOR . $image_name;
        if (copy($this->tmp_profile_image, $new_path)) {
            $this->profile_image = $image_name;
            $this->tmp_profile_image = null;
            $this->save(false);

            if (!empty($temp_dir))
                FileManager::deleteDir($temp_dir);

            $this->createThumbs($new_path, $image_name);
        }
    }

    /**
     * Create image thumbs
     * @param string $image_path
     * @param string $image_name
     *
     */
    protected function createThumbs($image_path, $image_name)
    {
        $sizes = [
            ['width' => 32, 'height' => 32],
            ['width' => 64, 'height' => 64],
            ['width' => 128, 'height' => 128],
            ['width' => 256, 'height' => 256],
        ];

        $base_dir = $this->getDir();
        foreach ($sizes as $size) {
            $thumb_name = $size['width'] . '_' . $image_name;
            $new_path = $base_dir . DIRECTORY_SEPARATOR . $thumb_name;
            // generate a thumbnail image
            Image::thumbnail($image_path, $size['width'], $size['height'])
                ->save($new_path, ['quality' => 50]);
        }
    }

    public static function getDefaultProfileImagePath()
    {
        return '@authModule/assets/src/img/user.png';
    }

    /**
     * Get profile image
     * @param integer $size
     * @return string
     */
    public function getProfileImageUrl($size = null)
    {
        $image_path = null;
        $base_dir = $this->getDir() . DIRECTORY_SEPARATOR;
        if (empty($this->profile_image)) {
            $image_path = static::getDefaultProfileImagePath();
        } elseif (!empty($size)) {
            $thumb = $base_dir . $size . '_' . $this->profile_image;
            $image_path = file_exists($thumb) ? $thumb : $base_dir . $this->profile_image;
        } else {
            $image_path = $base_dir . $this->profile_image;
        }

        if (!file_exists($image_path)) {
            $image_path = static::getDefaultProfileImagePath();
        }

        $asset = Yii::$app->getAssetManager()->publish($image_path);

        return $asset[1];
    }

    /**
     * @param integer $level_id
     * @param mixed $scenario
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public static function getInstance($level_id, $scenario = self::SCENARIO_CREATE)
    {
        $role_id = Roles::getScalar('id', ['level_id' => $level_id]);
        $config = [
            'class' => static::class,
            'scenario' => $scenario,
            'level_id' => $level_id,
        ];
        if ($scenario === self::SCENARIO_CREATE) {
            $config = array_merge($config, [
                'status' => Users::STATUS_ACTIVE,
                'send_email' => true,
                'role_id' => $role_id,
            ]);
        }

        return Yii::createObject($config);
    }

    /**
     * @param mixed $condition
     * @param bool $throwException
     * @return Users|null
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public static function loadModel($condition, $throwException = true)
    {
        $model = static::findOne($condition);
        if ($model === null) {
            if ($throwException) {
                throw new NotFoundHttpException('The requested resource was not found.');
            }
        }
        return $model;
    }
}
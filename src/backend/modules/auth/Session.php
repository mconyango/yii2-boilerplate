<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/21
 * Time: 7:47 PM
 */

namespace backend\modules\auth;


use backend\modules\auth\models\UserLevels;
use Yii;

class Session
{

    /**
     * Returns true if the currently logged in user is dev
     * @return bool
     */
    public static function isDev()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_DEV;
    }

    /**
     * Returns true if the currently logged in user is superadmin/data manager
     * @return bool
     */
    public static function isSuperAdmin()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_SUPER_ADMIN;
    }

    /**
     * Returns true if the currently logged in user is superadmin/data manager
     * @return bool
     */
    public static function isSystemAdmin()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_ADMIN;
    }

    /**
     * @return bool
     */
    public static function isOrganization()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_ORGANIZATION;
    }

    /**
     * @return bool
     */
    public static function isOrgClient()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->user->identity->level_id == UserLevels::LEVEL_ORGANIZATION_CLIENT;
    }

    /**
     * @return bool
     */
    public static function isPrivilegedAdmin()
    {
        return static::isDev() || static::isSuperAdmin() || static::isSystemAdmin();
    }

    /**
     * @return mixed
     */
    public static function accountId()
    {
        return Yii::$app->user->identity->org_id;
    }


    /**
     * @return int|string
     */
    public static function userId()
    {
        return Yii::$app->user->id;
    }

    /**
     * @return int|string
     */
    public static function userLevelId()
    {
        return Yii::$app->user->identity->level_id;
    }

    /**
     * @return int|string
     */
    public static function userRoleId()
    {
        return Yii::$app->user->identity->role_id;
    }

    /**
     * @return string
     */
    public static function userName()
    {
        return Yii::$app->user->identity->name;
    }

    /**
     * @return string
     */
    public static function userBranchId()
    {
        return Yii::$app->user->identity->branch_id;
    }

    /**
     * @return int|string
     */
    public static function getUserLevelId()
    {
        return Yii::$app->user->identity->level_id;

    }

    /**
     * @return string
     */
    public static function getName()
    {
        return Yii::$app->user->identity->name ?? null;
    }
}
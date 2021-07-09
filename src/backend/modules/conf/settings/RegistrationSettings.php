<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-01-18 12:08
 * Time: 12:08
 */

namespace backend\modules\conf\settings;


use common\helpers\Lang;

class RegistrationSettings extends BaseSettings
{
    const SECTION_MEMBER_REGISTRATION = 'member_registration';
    const KEY_MIN_AGE = 'minAge';
    const KEY_KIN_AGE_LIMIT= 'kinAgeLimit';

    /**
     * @var int
     */
    public $minAge;
    public $kinAgeLimit;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[self::KEY_MIN_AGE, self::KEY_KIN_AGE_LIMIT], 'required'],
            [['minAge', 'kinAgeLimit'], 'integer', 'min' => 1, 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            self::KEY_MIN_AGE => Lang::t('Minimum Age'),
            self::KEY_KIN_AGE_LIMIT => Lang::t('Kin Age Limit'),
        ];
    }

    /**
     * @return int
     */
    public static function getMinAge()
    {
        return (int)static::getSettingsComponent()->get(self::SECTION_MEMBER_REGISTRATION, self::KEY_MIN_AGE, 18);
    }

    /**
     * @return int
     */
    public static function getKinAgeLimit()
    {
        return (int)static::getSettingsComponent()->get(self::SECTION_MEMBER_REGISTRATION, self::KEY_KIN_AGE_LIMIT, 18);
    }
}
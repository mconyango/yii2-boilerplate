<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-10 17:55
 * Time: 17:55
 */

namespace backend\modules\conf\settings;


use common\helpers\Lang;
use common\helpers\Utils;
use kartik\password\StrengthValidator;

class PasswordSettings extends BaseSettings
{
    //settings constants;
    const SECTION_PASSWORD = 'password';
    const KEY_MIN_LENGTH = 'minLength';
    const KEY_MAX_LENGTH = 'maxLength';
    const KEY_MIN_LOWER = 'minLower';
    const KEY_MIN_UPPER = 'minUpper';
    const KEY_MIN_DIGIT = 'minDigit';
    const KEY_MIN_SPECIAL = 'minSpecial';
    const KEY_USE_PRESET = 'usePreset';
    const KEY_PRESET = 'preset';

    /**
     * @var int minimum number of characters. If not set, defaults to 8.
     */
    public $minLength;
    /**
     * @var int maximum length. If not set, it means no maximum length limit.
     */
    public $maxLength;
    /**
     * @var int minimal number of lower case characters
     */
    public $minLower;
    /**
     * @var int minimal number of upper case characters
     */
    public $minUpper;
    /**
     * @var int  minimal number of numeric digit characters
     */
    public $minDigit;
    /**
     * @var int minimal number of special characters
     */
    public $minSpecial;

    /**
     * @var bool
     */
    public $usePreset;

    /**
     * @var string
     */
    public $preset;

    public function rules()
    {
        return [
            [
                [self::KEY_MIN_LENGTH, self::KEY_MIN_LOWER, self::KEY_MIN_UPPER, self::KEY_MIN_DIGIT, self::KEY_MIN_SPECIAL],
                'required',
                'when' => function () {
                    return !$this->usePreset;
                }
            ],
            [
                [self::KEY_PRESET],
                'required',
                'when' => function () {
                    return $this->usePreset;
                }
            ],
            [
                [self::KEY_MIN_LENGTH, self::KEY_MAX_LENGTH, self::KEY_MIN_LOWER, self::KEY_MIN_UPPER, self::KEY_MIN_DIGIT, self::KEY_MIN_SPECIAL],
                'integer',
                'min' => 0
            ],
            [[self::KEY_USE_PRESET], 'safe'],
            [self::KEY_MIN_LENGTH, 'compare', 'compareAttribute' => self::KEY_MAX_LENGTH, 'operator' => '<=', 'type' => 'number'],
        ];
    }


    public function attributeLabels()
    {
        return [
            self::KEY_MIN_LENGTH => Lang::t('Minimum password length'),
            self::KEY_MAX_LENGTH => Lang::t('Maximum password length'),
            self::KEY_MIN_LOWER => Lang::t('Minimum lower case characters'),
            self::KEY_MIN_UPPER => Lang::t('Minimum upper case characters'),
            self::KEY_MIN_DIGIT => Lang::t('Minimum numeric characters'),
            self::KEY_MIN_SPECIAL => Lang::t('Minimum special characters'),
            self::KEY_USE_PRESET => Lang::t('Use preset'),
            self::KEY_PRESET => Lang::t('Preset'),
        ];
    }

    /**
     * @param bool $tip
     * @return array
     */
    public static function presetOptions($tip = false)
    {
        return Utils::appendDropDownListPrompt([
            StrengthValidator::SIMPLE => 'Simple',
            StrengthValidator::NORMAL => 'Normal',
            StrengthValidator::FAIR => 'Fair',
            StrengthValidator::MEDIUM => 'Medium',
            StrengthValidator::STRONG => 'Strong',
        ], $tip);
    }

    /**
     * @return string
     */
    public static function getMinLength()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MIN_LENGTH, 8);
    }

    /**
     * @return string
     */
    public static function getMaxLength()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MAX_LENGTH, null);
    }

    /**
     * @return string
     */
    public static function getMinLower()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MIN_LOWER, 2);
    }

    /**
     * @return string
     */
    public static function getMinUpper()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MIN_UPPER, 2);
    }

    /**
     * @return string
     */
    public static function getMinDigit()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MIN_DIGIT, 2);
    }

    /**
     * @return string
     */
    public static function getMinSpecial()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_MIN_SPECIAL, 2);
    }

    /**
     * @return string
     */
    public static function getUsePreset()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_USE_PRESET, true);
    }

    /**
     * @return string
     */
    public static function getPreset()
    {
        return static::getSettingsComponent()->get(self::SECTION_PASSWORD, self::KEY_PRESET, StrengthValidator::NORMAL);
    }
}
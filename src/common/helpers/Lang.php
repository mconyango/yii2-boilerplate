<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/20
 * Time: 6:51 PM
 */

namespace common\helpers;

use Yii;

class Lang
{
    /**
     * @param string $text
     * @param array $params
     * @param string $module
     * @param string|null $language
     * @return string
     */
    public static function t($text, $params = [], $module = 'app', $language = null)
    {
        return Yii::t($module, $text, $params, $language);
    }
}
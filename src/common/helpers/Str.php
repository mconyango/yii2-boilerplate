<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-03-15 9:49 PM
 */

namespace common\helpers;


class Str extends \Illuminate\Support\Str
{
    /**
     * @param string $string
     * @return mixed
     */
    public static function removeWhitespace($string)
    {
        return preg_replace('/\s+/', '', $string);
    }

    /**
     * @param string $string
     * @return null|string
     */
    public static function extractJsonFromString($string)
    {
        preg_match('~\{(?:[^{}]|(?R))*\}~', $string, $matches);
        if (!empty($matches))
            return $matches[0];
        return null;
    }

    /**
     * @param string $string
     * @return bool
     */
    public static function isEmpty($string): bool
    {
        return !($string == "0" || $string);
    }

    /**
     * @param string $string
     * @return string
     */
    public static function removeNonNumericCharacters(string $string): string
    {
        return preg_replace("/[^0-9]/", "", $string);
    }
}
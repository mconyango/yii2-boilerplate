<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-10-29
 * Time: 10:56 PM
 */

namespace common\helpers;


class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * @param array $array
     * @param $key
     * @param $value
     * @param bool $returnAll
     * @return array
     */
    public static function search($array, $key, $value, $returnAll = false): array
    {
        $results = [];
        static::searchR($array, $key, $value, $results);
        if ($returnAll) {
            return $results;
        }
        return $results[0] ?? [];
    }

    protected static function searchR($array, $key, $value, &$results)
    {
        if (!is_array($array)) {
            return;
        }

        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }

        foreach ($array as $subarray) {
            static::searchR($subarray, $key, $value, $results);
        }
    }

    /**
     * @param string|int $needle
     * @param array $haystack
     * @return bool
     */
    public static function InArrayCaseInsensitive($needle, $haystack)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }

    /**
     * @param string|int $needle
     * @param array $haystack
     * @return false|int|string
     */
    public static function arraySearchCaseInsensitive($needle, $haystack)
    {
        return array_search(strtolower($needle), array_map('strtolower', $haystack));
    }
}
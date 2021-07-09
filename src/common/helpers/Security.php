<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/08/17
 * Time: 6:41 PM
 */

namespace common\helpers;


class Security
{
    /**
     * Generates a random integer
     * @param int|null $min
     * @param int|null $max
     * @return int
     */
    public static function generateRandomInt($min = null, $max = null)
    {
        return mt_rand($min,$max);
    }
}
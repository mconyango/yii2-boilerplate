<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/04
 * Time: 11:36 PM
 */

namespace common\helpers;


class Url extends \yii\helpers\Url
{
    const GET_PARAM_RETURN_URL = 'r_url';

    /**
     * Get return link
     * @param string|array $url
     * @return string
     */
    public static function getReturnUrl($url = NULL)
    {
        $rl = filter_input(INPUT_GET, self::GET_PARAM_RETURN_URL);
        return !empty($rl) ? $rl : Url::to($url);
    }
}
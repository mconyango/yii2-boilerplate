<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/06/16
 * Time: 1:59 AM
 */

namespace common\helpers;


class Msisdn
{
    /**
     * Format a phone number by appending the country code
     * @param string $phone
     * @param string $country_code
     * @return string
     */
    public static function format($phone, $country_code = '254')
    {
        //removes all non-numeric characters
        $phone = Str::removeNonNumericCharacters($phone);
        //removes any leading zero
        $phone = (int)$phone;
        //25407....
        $country_code_length = strlen($country_code);
        if (substr($phone, 0, $country_code_length) == $country_code) {
            if (substr($phone, $country_code_length, 1) == "0")
                $phone = (int)substr($phone, $country_code_length);
            else
                return $phone;
        }

        if (strlen($phone) === 9)
            return $country_code . $phone;

        return $phone;
    }
}
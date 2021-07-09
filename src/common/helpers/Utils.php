<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/11/24
 * Time: 10:58 AM
 */

namespace common\helpers;


use Carbon\Carbon;
use Illuminate\Support\Str;
use NumberFormatter;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\helpers\Html;

class Utils
{
    /**
     *Checks whether the app is running as web app or console app.
     * @return boolean TRUE=web, FALSE=console
     */
    public static function isWebApp()
    {
        return (Yii::$app instanceof \yii\web\Application);
    }

    /**
     * Hashes a string based on an algorithm. Simple stuff
     *
     * @param null $string
     * @param string $algo
     * @param bool $raw_output
     * @return string
     */
    public static function generateHash($string, $algo = 'sha256', $raw_output = FALSE)
    {
        return hash($algo, $string, $raw_output);
    }

    /**
     * Converts xml to json
     *
     * @param $xml
     * @return string
     */
    public static function convertXmlToJson($xml)
    {
        $xml = str_replace(["\n", "\r", "\t"], '', $xml);

        $xml = trim(str_replace('"', "'", $xml));

        $simpleXml = simplexml_load_string($xml);

        $json = json_encode($simpleXml);

        return $json;
    }

    /**
     * Checks if a string is a valid timestamp.
     * https://gist.github.com/sepehr/6351385
     *
     * @param  string $timestamp Timestamp to validate.
     *
     * @return bool
     */
    public static function is_timestamp($timestamp)
    {
        $check = (is_int($timestamp) OR is_float($timestamp))
            ? $timestamp
            : (string)(int)$timestamp;
        return ($check === $timestamp)
            AND ((int)$timestamp <= PHP_INT_MAX)
            AND ((int)$timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Get the client browser type
     *
     * @return string
     */
    public static function getBrowser()
    {
        return Yii::$app->request->getUserAgent();
    }

    /**
     * This function gets the Ip of visitors
     */
    public static function getIp()
    {
        return Yii::$app->request->getUserIP();
    }

    /**
     * get date filter params
     * @param array $params
     * @return array <pre>
     */
    public static function getDateFilterParams(array $params)
    {
        $start_date = Carbon::now();
        $end_date = array_get($params, 'end_date');
        $date_field = array_get($params, 'date_created');
        if (array_has($params, 'enforce_defaults')) {
            if (empty(array_get($params, 'start_date'))) {
                $start_date = date('Y-m-d', strtotime('first day of last month'));
                $end_date = date('Y-m-d', strtotime('last day of last month'));
            }

            if (empty($end_date)) {
                $end_date = $start_date;
            }

        }

        $title = DateUtils::formatDate($start_date, 'D dS M y');
        if ($end_date !== $start_date) {
            $title .= ' - ' . DateUtils::formatDate($end_date, 'D dS M y');
            return [$start_date, $end_date, $date_field, $title];
        }
        return [$start_date, $end_date, $date_field, $title];
    }

    /**
     * Return a string value given a boolean value
     * 1=Yes,0=No
     * @param boolean $bool
     * @return string
     */
    public static function decodeBoolean($bool)
    {
        return Lang::t($bool ? 'YES' : 'NO');
    }

    /**
     * Return boolean options in array used in drop-down list
     * e.g
     * <pre>array("1"=>"Yes","0"=>"No")</pre>
     * @param bool $hint
     * @return array
     */
    public static function booleanOptions($hint = false)
    {
        return static::appendDropDownListPrompt([
            '1' => static::decodeBoolean(1),
            '0' => static::decodeBoolean(0),
        ], $hint);
    }

    /**
     * Clean a string by removing space and special characters
     * @param string $string
     * @param string $space_holder
     * @param bool $allow_numbers
     * @return string
     */
    public static function cleanString($string, $space_holder = '_', $allow_numbers = true)
    {
        if (!empty($space_holder))
            return str_replace(" ", $space_holder, $string);
        else if ($allow_numbers)
            return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        else
            return preg_replace("/[^a-zA-Z]+/", "", $string); // Removes special chars & numbers.
    }

    /**
     * Generate integer lists
     * @param integer $start
     * @param integer $end
     * @param int $gap
     * @return array|bool
     */
    public static function generateIntegersList($start, $end, $gap = 1)
    {
        if (!is_numeric($start) || !is_numeric($end) || !is_numeric($gap))
            throw new \InvalidArgumentException('All params must be a number.');
        if ($start > $end || $gap > $end)
            throw new \InvalidArgumentException('Check your params.');
        $list = [];

        for ($i = $start; $i <= $end; $i += $gap) {
            $list[$i] = $i;
        }

        return $list;
    }

    /**
     * Wraps a long string.
     * @param string $string
     * @param int $width
     * @param string $break
     * @return string
     */
    public static function smartWordwrap($string, $width = 75, $break = "\n")
    {
        if (empty($string))
            return NULL;
        // split on problem words over the line length
        $pattern = sprintf('/([^ ]{%d,})/', $width);
        $output = '';
        $words = preg_split($pattern, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            if (false !== strpos($word, ' ')) {
                // normal behaviour, rebuild the string
                $output .= $word;
            } else {
                // work out how many characters would be on the current line
                $wrapped = explode($break, wordwrap($output, $width, $break));
                $count = $width - (strlen(end($wrapped)) % $width);

                // fill the current line and add a break
                $output .= substr($word, 0, $count) . $break;

                // wrap any remaining characters from the problem word
                $output .= wordwrap(substr($word, $count), $width, $break, true);
            }
        }

        // wrap the final output
        if (strlen($output) <= $width)
            return $output;
        return wordwrap($output, $width, $break);
    }

    /**
     * Calculates a percentage.
     * @param int $numerator
     * @param int $denominator
     * @param int $precision
     * @return int
     */
    public static function calculatePercentage($numerator, $denominator, $precision = 0)
    {
        if ((int)$denominator === 0)
            return 0;
        $percentage = ($numerator / $denominator) * 100;
        return round($percentage, $precision);
    }

    /**
     * Adopted from laravel framework
     *
     * @return string the pluralized word
     */
    public static function pluralize($name)
    {
        return Str::plural($name);
    }

    /**
     * sanitize an input and remove all non-digit characters
     * @param $string
     * @return string
     */
    public static function parseInt($string)
    {
        return (int)preg_replace('/\D/', '', $string);
    }

    /**
     * Truncate a string
     * @param string $string
     * @param integer $max_length
     * @param string $suffix
     * @return string
     */
    public static function trancateString($string, $max_length, $suffix = '...')
    {
        return Str::limit($string, $max_length, $suffix);
    }

    /**
     * Shortens a long string and inserts a suffix
     * @param $string
     * @param $maxLength
     * @param string $suffix
     * @param null $word_wrap_width
     * @return string
     */
    public static function myShortenedString($string, $maxLength, $suffix = '...', $word_wrap_width = null)
    {
        $string = Html::encode($string);
        if (strlen($string) > $maxLength)
            $new_string = substr($string, 0, $maxLength);
        else
            $new_string = $string;
        if ($word_wrap_width && strlen($new_string) > $word_wrap_width)
            $new_string = static::smartWordwrap($new_string, $word_wrap_width, '<br/>');
        if (strlen($string) > $maxLength)
            $new_string .= $suffix;
        return $new_string;
    }

    /**
     * add ordinal number suffix
     * @param integer $num
     * @return string
     */
    public static function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), [11, 12, 13])) {
            switch ($num % 10) {
                // Handle 1st, 2nd, 3rd
                case 1:
                    return $num . 'st';
                case 2:
                    return $num . 'nd';
                case 3:
                    return $num . 'rd';
            }
        }
        return $num . 'th';
    }

    /**
     * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
     * @param    int    number to convert
     * @param bool $uppercase
     * @return string letters from number input
     * @internal param case $boolean the letter on return?
     */
    public static function numToletter($num, $uppercase = FALSE)
    {
        $num -= 1;

        $letter = chr(($num % 26) + 97);
        $letter .= (floor($num / 26) > 0) ? str_repeat($letter, floor($num / 26)) : '';
        return ($uppercase ? strtoupper($letter) : $letter);
    }

    /**
     * Takes a letter and converts it to number
     * @access    public
     * @param $string
     * @return int number from letter input
     */
    public static function lettersToNum($string)
    {
        $num = 0;
        $string = strtolower($string);
        $string = str_split($string);
        $exp = count($string) - 1;
        foreach ($string as $char) {
            $digit = ord($char) - 96;
            $num += $digit * pow(26, $exp);

            $exp--;
        }

        return $num;
    }

    /**
     * Adds either http:// when the URL do not have the protocol prefix
     * @param string $url
     * @return string $url
     */
    public static function prepareUrl($url)
    {
        if (empty($url))
            return '#';
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
            $url = 'http://' . $url;
        return $url;
    }

    /**
     * Generates UUID
     * @return string
     */
    public static function uuid()
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Create a unique 64 bit integer from a string
     * This method depends of the GMP php extension/module
     * @param string|null $string
     * @return string
     */
    public static function createIntFromString($string = null)
    {
        if (empty($string))
            $string = self::uuid();
        return gmp_strval(gmp_init(substr(md5($string), 0, 16), 16), 10);
    }

    /**
     * This function generates a random string that can be used as salt
     * @return string
     */
    public static function generateSalt()
    {
        return static::uuid();
    }

    /**
     * Default num format
     *
     * @param $number
     * @param int $dps
     * @return string
     */
    public static function formatNumber($number, $dps = 2)
    {

        // other args can be left as is
        return number_format($number, $dps);
    }

    /**
     * Format a percentage
     *
     * @param $number
     * @return string
     */
    public static function formatPercentage($number)
    {

        return sprintf("%.2f%%", $number);
    }

    /**
     * Format money, to specific currency and locale. Will default to en-US if locale is null
     * intl extension required. Its required by YII anyway, so we are safe
     * @param $amount
     * @param string $currency
     * @param null $locale
     * @return string
     */
    public static function formatMoney($amount, $currency = null, $locale = null)
    {
        $locale = $locale === null ? Yii::$app->language : $locale;
        $currency = $currency === null ? 'KES' : $currency;

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency) . PHP_EOL;
    }

    /**
     * The units thing
     *
     * @param $value
     * @return string
     */
    public static function formatUnits($value)
    {

        $value = number_format($value);
        $def = Lang::t('unit');

        if ($value == 1) {
            return "{$value} {$def}";
        } else {
            $def = Str::plural($def);
            return "{$value} {$def}";
        }
    }

    /**
     * @param $listData
     * @param mixed $prompt
     * @return array
     */
    public static function appendDropDownListPrompt($listData, $prompt = false)
    {
        $options = [];
        if ($prompt !== false && null !== $prompt) {
            $prompt = $prompt === true ? "[select one]" : $prompt;
            $options[''] = $prompt;
        }

        return $options + $listData;
    }
}
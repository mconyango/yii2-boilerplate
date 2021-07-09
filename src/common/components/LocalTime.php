<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/05/20
 * Time: 10:57 AM
 */

namespace common\components;


use backend\modules\conf\settings\SystemSettings;
use common\helpers\Utils;
use DateTime;
use DateTimeZone;
use Yii;
use yii\base\Component;

/**
 *       Notes
 * 1.    Column field types must be timestamp not date, time, datetime
 *       This is because timestamp columns are stored as UTC then converted to the specified timezone
 *       date, time and datetime columns don't save the timezone
 * 2.    Set the timezone to UTC in protected/config/main.php
 *       so that all retrieved times are in the UTC timezone for consistency
 *       'db'=>array(
 *       ...
 *       'initSQLs'=>array("set names utf8;set time_zone='+00:00';"),
 *       ),
 * 3.    When using phpMyAdmin, just use "set time_zone='+00:00'"
 *       or whatever timezone you require to display timestamps in your zone
 *
 * 4.    After a user logs in call Yii::$app->localTime->setTimeZone('Europe/London');
 */
class LocalTime extends Component
{
    // Used for setting/getting the global variable - change this if there are conflicts

    const _GLOBAL_TIMEZONE = 'LocalTime_timezone';
    const _USER_TIMEZONE = '_user_timeZone';
    // Default server time
    const _UTC = 'UTC';

    /**
     * Set the timezone - usually after the user has logged in
     * Use one of the supported timezones, eg: Europe/London as this will calculate daylight saving hours
     * http://php.net/manual/en/timezones.php
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        if (Utils::isWebApp())
            Yii::$app->session->set(self::_USER_TIMEZONE, $timezone);
    }

    /**
     * Return the current timezone
     * @return string
     */
    public function getTimezone()
    {
        $timezone = SystemSettings::getDefaultTimezone();
        if (Utils::isWebApp()) {
            $timezone = Yii::$app->session->get(self::_USER_TIMEZONE, $timezone);
        }

        return $timezone;
    }


    /**
     * Local now() function
     * Can use any of the php date() formats to return the local date/time value
     * http://php.net/manual/en/function.date.php
     * @param  string $format
     * @return string
     * @throws \Exception
     */
    public function getLocalNow($format = DATE_ISO8601)
    {
        $local_now = new DateTime(null, $this->getLocalDateTimeZone());
        return $local_now->format($format);
    }

    /**
     *  UTC Now() function
     * Can use any of the php date() formats to return the UTC date/time value
     * http://php.net/manual/en/function.date.php
     * @param  string $format
     * @return string
     * @throws \Exception
     */
    public function getUTCNow($format = DATE_ISO8601)
    {
        $utc_now = new DateTime(null, $this->getServerDateTimeZone());
        return $utc_now->format($format);
    }

    /**
     * Return a DateTimezone object for the local time
     * @param string $timezone
     * @return DateTimeZone
     */
    public function getLocalDateTimeZone($timezone = null)
    {
        if ($timezone === null)
            $timezone = $this->getTimezone();

        $datetimezone = new DateTimeZone($timezone);

        return $datetimezone;
    }

    /**
     * Return a datetimezone object for UTC
     * @return DateTimeZone
     */
    public function getServerDateTimeZone()
    {
        $datetimezone = new DateTimeZone(self::_UTC);
        return $datetimezone;
    }

    /**
     * Converts a timestamp from UTC to a local time
     * Expects a date in Y-m-d H:i:s type format and assumes it is UTC
     * Returns a date in the local time zone
     * @param string $servertimestamp
     * @return string
     * @throws \Exception
     */
    public function fromUTC($servertimestamp)
    {
        // Its okay if an ISO8601 time is passed because the timezone in the string will be used and the _serverDateTimeZone object is ignored
        $localtime = new DateTime($servertimestamp, $this->getServerDateTimeZone());
        // Then set the timezone to local and it will be automatically updated, even allowing for daylight saving
        $localtime->setTimeZone($this->getLocalDateTimeZone());
        // Return as 2010-08-15 15:52:01 for use in the yii app
        return $localtime->format('Y-m-d H:i:s');
    }

    /**
     * Converts a local timestamp to UTC
     * Expects a date in Y-m-d H:i:s format and assumes it is the local time zone
     * Returns an ISO date in the UTC zone
     * @param string $localtimestamp
     * @return string
     * @throws \Exception
     */
    public function toUTC($localtimestamp)
    {
        // Create an object using the local time zone - this will account for daylight saving
        $server_time = new DateTime($localtimestamp, $this->getLocalDateTimeZone());
        // Then set the timezone to UTC and it will be automatically updated
        // In theory this step isn't needed if using the ISO8601 format.
        $server_time->setTimeZone($this->getServerDateTimeZone());

        // Return as 2010-08-15T15:52:01+0000 so the timestamp column is properly updated
        return ($server_time->format(DATE_ISO8601));
    }

    /**
     * Use in afterFind
     * Ensure that the SQL "set time_zone='+00:00'" has been set
     * Returns a date/time combination based on the current locale
     * Expects a date/time in the yyyy-mm-dd hh:mm:ss type format
     * @param string $servertimestamp
     * @param string $customFormat Date format e.g M jS Y g:i a
     * @return string
     * @throws \Exception
     */
    public function toLocalDateTime($servertimestamp, $customFormat = null)
    {
        if (empty($servertimestamp))
            return NULL;
        // Create a server datetime object
        $localtime = new DateTime($servertimestamp, $this->getServerDateTimeZone());

        // Then set the timezone to local and it will be automatically updated, even allowing for daylight saving
        $localtime->setTimeZone($this->getLocalDateTimeZone());

        // Return as a local datetime
        return ($localtime->format($customFormat));
    }

    /**
     * Use in beforeSave
     * Converts a date/time string in the locale format to an ISO time for saving to the server
     * eg 31/12/2011 will become 2011-12-31T00:00:00+0000
     * @param string $localtime
     * @param string $local_format
     * @return string
     * @throws \Exception
     */
    public function fromLocalDateTime($localtime, $local_format = 'yyyy-MM-dd hh:mm:ss')
    {
        // Uses a modified CDateTimeParser that defaults the time values rather than return false
        // Also returns a time string rather than a timestamp just in case the timestamp is the wrong timezone
        $defaults = ['year' => $this->getLocalNow('Y'), 'month' => $this->getLocalNow('m'), 'day' => $this->getLocalNow('d'), 'hour' => 0, 'minute' => 0, 'second' => 0];
        $time_values = $this->parse($localtime, $local_format, $defaults);
        // Create a new date time in the local timezone
        $server_time = new DateTime($time_values, $this->getLocalDateTimeZone());

        // Set the timezone to UTC
        $server_time = $server_time->setTimeZone($this->getServerDateTimeZone());

        // Return it as an iso date ready for saving
        return ($server_time->format(DATE_ISO8601));
    }

    /**
     * DefaultDateTimeParser converts a date/time string to an array
     *
     * The following pattern characters are recognized:
     * <pre>
     * Pattern |      Description
     * ----------------------------------------------------
     * d       | Day of month 1 to 31, no padding
     * dd      | Day of month 01 to 31, zero leading
     * M       | Month digit 1 to 12, no padding
     * MM      | Month digit 01 to 12, zero leading
     * yy      | 2 year digit, e.g., 96, 05
     * yyyy    | 4 year digit, e.g., 2005
     * h       | Hour in 0 to 23, no padding
     * hh      | Hour in 00 to 23, zero leading
     * H       | Hour in 0 to 23, no padding
     * HH      | Hour in 00 to 23, zero leading
     * m       | Minutes in 0 to 59, no padding
     * mm      | Minutes in 00 to 59, zero leading
     * s       | Seconds in 0 to 59, no padding
     * ss      | Seconds in 00 to 59, zero leading
     * a       | AM or PM, case-insensitive (since version 1.1.5)
     * ----------------------------------------------------
     * </pre>
     *
     *
     * Modified version of http://www.yiiframework.com/doc/api/1.1/CDateTimeParser
     *
     * This version will accept a pattern and default the time values for any missing pattern
     * It returns a string rather than a timestamp in case its the wrong timezone
     * Also uses the LocalTime class to get the time for now() in the users timezone
     * For example, DefaultDateTimeParser::parse('31/12/2011','dd/MM/yyyy',array('hour'=>0,'minute'=>0,'day'=>0);
     * Will return '2011-12-2011 0:0:0'
     *
     * @param $value
     * @param string $pattern
     * @param array $defaults
     * @return bool|string
     * @throws \Exception
     */
    public function parse($value, $pattern = 'MM/dd/yyyy', $defaults = [])
    {
        $tokens = self::tokenize($pattern);
        $i = 0;
        $n = strlen($value);
        foreach ($tokens as $token) {
            switch ($token) {
                case 'yyyy':
                    {
                        if (($year = self::parseInteger($value, $i, 4, 4)) !== null)
                            $i += 4;
                        break;
                    }
                case 'yy':
                    {
                        if (($year = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($year);
                        break;
                    }
                case 'MM':
                    {
                        if (($month = self::parseInteger($value, $i, 2, 2)) !== null)
                            $i += 2;
                        break;
                    }
                case 'M':
                    {
                        if (($month = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($month);
                        break;
                    }
                case 'dd':
                    {
                        if (($day = self::parseInteger($value, $i, 2, 2)) !== null)
                            $i += 2;
                        break;
                    }
                case 'd':
                    {
                        if (($day = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($day);
                        break;
                    }
                case 'h':
                case 'H':
                    {
                        if (($hour = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($hour);
                        break;
                    }
                case 'hh':
                case 'HH':
                    {
                        if (($hour = self::parseInteger($value, $i, 2, 2)) !== null)
                            $i += 2;
                        break;
                    }
                case 'm':
                    {
                        if (($minute = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($minute);
                        break;
                    }
                case 'mm':
                    {
                        if (($minute = self::parseInteger($value, $i, 2, 2)) !== null)
                            $i += 2;
                        break;
                    }
                case 's':
                    {
                        if (($second = self::parseInteger($value, $i, 1, 2)) !== null)
                            $i += strlen($second);
                        break;
                    }
                case 'ss':
                    {
                        if (($second = self::parseInteger($value, $i, 2, 2)) !== null)
                            $i += 2;
                        break;
                    }
                case 'a':
                    {
                        // If this value isn't present then ignore it
                        if (($ampm = self::parseAmPm($value, $i)) === null)
                            break;

                        if (isset($hour)) {
                            if ($hour == 12 && $ampm === 'am')
                                $hour = 0;
                            else if ($hour < 12 && $ampm === 'pm')
                                $hour += 12;
                        }
                        $i += 2;
                        break;
                    }
                default:
                    {
                        // If the separator pattern doesn't exist in the value, then ignore it
                        // eg: a space
                        if (strpos($value, $token) === false)
                            break;

                        $tn = strlen($token);
                        if ($i >= $n || substr($value, $i, $tn) !== $token)
                            return false;
                        $i += $tn;
                        break;
                    }
            }
        }
        if ($i < $n) // somethings gone wrong
            return false;

        if (!isset($year))
            $year = isset($defaults['year']) ? $defaults['year'] : $this->getLocalNow('Y'); // date('Y');
        if (!isset($month))
            $month = isset($defaults['month']) ? $defaults['month'] : $this->getLocalNow('n'); // date('n');
        if (!isset($day))
            $day = isset($defaults['day']) ? $defaults['day'] : $this->getLocalNow('j'); // date('j');
        if (!isset($hour))
            $hour = isset($defaults['hour']) ? $defaults['hour'] : $this->getLocalNow('H'); // date('H');
        if (!isset($minute))
            $minute = isset($defaults['minute']) ? $defaults['minute'] : $this->getLocalNow('i'); // date('i');
        if (!isset($second))
            $second = isset($defaults['second']) ? $defaults['second'] : $this->getLocalNow('s'); // date('s');

        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;
        $hour = (int)$hour;
        $minute = (int)$minute;
        $second = (int)$second;


        if (static::isValidDate($year, $month, $day) && static::isValidTime($hour, $minute, $second)) {
            // Return a time string rather than a timestamp because the timestamp might be the wrong timezone?
            return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute . ':' . $second;
        } else
            return false;
    }


    /**
     * @param string $pattern the pattern that the date string is following
     * @return array
     */
    private static function tokenize($pattern)
    {
        if (!($n = strlen($pattern)))
            return [];
        $tokens = [];
        for ($c0 = $pattern[0], $start = 0, $i = 1; $i < $n; ++$i) {
            if (($c = $pattern[$i]) !== $c0) {
                $tokens[] = substr($pattern, $start, $i - $start);
                $c0 = $c;
                $start = $i;
            }
        }
        $tokens[] = substr($pattern, $start, $n - $start);
        return $tokens;
    }

    /**
     * @param string $value the date string to be parsed
     * @param integer $offset starting offset
     * @param integer $minLength minimum length
     * @param integer $maxLength maximum length
     * @return null|string
     */
    protected static function parseInteger($value, $offset, $minLength, $maxLength)
    {
        for ($len = $maxLength; $len >= $minLength; --$len) {
            $v = substr($value, $offset, $len);
            if (ctype_digit($v) && strlen($v) >= $minLength)
                return $v;
        }
        // Changed by Russell England to null rather than false
        return null;
    }

    /*
     * @param string $value the date string to be parsed
     * @param integer $offset starting offset
     */

    protected static function parseAmPm($value, $offset)
    {
        $v = strtolower(substr($value, $offset, 2));
        return $v === 'am' || $v === 'pm' ? $v : false;
    }

    /**
     * Checks to see if the year, month, day are valid combination.
     * @param integer $y year
     * @param integer $m month
     * @param integer $d day
     * @return boolean true if valid date, semantic check only.
     */
    public static function isValidDate($y, $m, $d)
    {
        return checkdate($m, $d, $y);
    }

    /**
     * Checks to see if the hour, minute and second are valid.
     * @param integer $h hour
     * @param integer $m minute
     * @param integer $s second
     * @param boolean $hs24 whether the hours should be 0 through 23 (default) or 1 through 12.
     * @return boolean true if valid date, semantic check only.
     */
    public static function isValidTime($h, $m, $s, $hs24 = true)
    {
        if ($hs24 && ($h < 0 || $h > 23) || !$hs24 && ($h < 1 || $h > 12))
            return false;
        if ($m > 59 || $m < 0)
            return false;
        if ($s > 59 || $s < 0)
            return false;
        return true;
    }
}
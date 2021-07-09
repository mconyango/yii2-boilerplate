<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 5:47 PM
 */

namespace common\helpers;


use Carbon\Carbon;
use common\components\LocalTime;
use DateTime;
use Yii;

class DateUtils
{
    const REPORTS_DURATION_DAILY = '1';
    const REPORTS_DURATION_WEEKLY = '2';
    const REPORTS_DURATION_MONTHLY = '3';
    const REPORTS_DURATION_YEARLY = '4';

    /**
     * This function extends a date by adding either days,weeks,moths or years to a date
     * @param string $date
     * @param string $length
     * @param string $type either hour,day,week,month,year
     * @param bool|string $format A valid date format
     * @return bool|string
     * @throws \Exception
     */
    public static function addDate($date, $length, $type = 'month', $format = 'Y-m-d')
    {
        switch ($type) {
            case 'hour':
                return Carbon::parse($date)->addHour($length)->format($format);
            case 'day':
                return Carbon::parse($date)->addDay($length)->format($format);
            case 'week':
                return Carbon::parse($date)->addWeek($length)->format($format);
            case 'month':
                return Carbon::parse($date)->addMonth($length)->format($format);
            case 'year':
                return Carbon::parse($date)->addYear($length)->format($format);
            default:
                return $date;
        }
    }

    /**
     * This function will format the date given into a user friendly format,taking into consideration the timezone of the user
     * @param string $date the date to be formatted
     * @param string $format date format default= 31/05/2012 03:51 PM
     * @param bool $toLocalTime
     * @return string the formatted date
     * @throws \Exception
     */
    public static function formatToLocalDate($date, $format = 'd-M-Y g:i a', $toLocalTime = true)
    {
        if (empty($date))
            return NULL;

        if (is_numeric($date))
            $date = date("Y-m-d H:i:s", $date);
        elseif (!static::checkDateTime($date))
            return $date;

        if ($toLocalTime) {
            /* @var $localTime LocalTime */
            $localTime = Yii::$app->localTime;
            return $localTime->toLocalDateTime($date, $format);
        }

        $date = date_create($date);
        return date_format($date, $format);
    }

    /**
     * This function will format the date given into a user friendly format,taking into consideration the timezone of the user
     *
     * @param string $date the date to be formatted
     * @param string $format date format default= 31/05/2012 03:51 PM
     * @param null $timezone
     * @return string the formatted date
     */
    public static function formatDate($date, $format = 'd-M-Y g:i a', $timezone = null)
    {
        if (empty($date))
            return null;

        if (is_int($date)) {
            return Carbon::createFromTimestamp($date)->format($format);
        }
        return Carbon::parse($date, $timezone)->format($format);
    }


    /**
     * Check whether a given string is a valid date. Using PHP5.5's DateTime class
     *
     * @param string $dateString
     * @return boolean
     */
    public static function isValidDate($dateString)
    {
        return (DateTime::createFromFormat('m/d/Y', $dateString) !== false);
    }

    /**
     * Make a timestamp
     *
     * @param null $tz
     * @return int
     */
    public static function makeTimeStamp($tz = null)
    {
        return $tz === null ?
            time() :
            Carbon::now()->timezone($tz)->getTimestamp();
    }

    /**
     * Format a timestamp to a date. Assume all timestamps are UTC, but we will use tz if specified
     *
     * @param $timestamp
     * @param string $format
     * @param null|string $tz
     * @return string
     */
    public static function formatTimeStamp($timestamp, $format = "d/m/Y g:i a", $tz = "Africa/Nairobi")
    {
        //  // Lang::t('{0, date}', $this->created_at)
        // do we have a valid timestamp?
        if (Utils::is_timestamp($timestamp)) {
            return $tz === null ?
                Carbon::createFromTimestampUTC($timestamp)->format($format) :
                Carbon::createFromTimestamp($timestamp)->timezone($tz)->format($format);
        }
        // just default to a 1970
        return Carbon::createFromTimestamp(null)->format($format);

    }

    /**
     * Format a date to MYSQL supported format
     *
     * @param $date
     * @return string
     */
    public static function formatToMysql($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public static function mysqlTimestamp()
    {
        return self::formatDate(time(), 'Y-m-d H:i:s');
    }

    /**
     * Format the sample date into a user friendly format e,g '14/10/1987 12:00 am'
     *
     * @param string $format
     * @return string
     */
    public static function formatSampleDate($format = 'd/m/Y g:i a')
    {
        return Carbon::parse('14th October 1987')->format($format);
    }


    /**
     * Checks if one date is greater than another date
     * @param string $date1
     * @param string $date2
     * @param bool $strict To use greater than only, or include equals
     * @return bool True if date2 is greater than $date1 else false
     */
    public static function isGreaterThan($date1, $date2, $strict = true)
    {
        // format the dates correctly
        $date1 = self::formatDate($date1);
        $date2 = self::formatDate($date2);
        return $strict ? Carbon::parse($date1)->gt(Carbon::parse($date2)) :
            Carbon::parse($date1)->gte(Carbon::parse($date2));
    }

    /**
     * Checks if one date is less than another date
     * @param string $date1
     * @param string $date2
     * @param bool $strict To use less than only, or include equals
     * @return bool True if date2 is greater than $date1 else false
     */
    public static function isLessThan($date1, $date2, $strict = true)
    {
        // format the dates correctly
        $date1 = self::formatDate($date1);
        $date2 = self::formatDate($date2);
        return $strict ? Carbon::parse($date1)->lt(Carbon::parse($date2)) :
            Carbon::parse($date1)->lte(Carbon::parse($date2));
    }

    /**
     * Get all the days of a given week
     * @param string $week_number e.g "01","02","22"
     * @param string $year e.g "2012" or 2012
     * @param string $format .The date format
     * @return array
     */
    public static function getDaysOfWeek($week_number = NULL, $year = NULL, $format = 'Y-m-d')
    {
        if (empty($week_number))
            $week_number = date('W');
        if (empty($year))
            $year = date('Y');
        $days_of_week = [];
        if (is_integer($week_number))
            $week_number = str_pad($week_number, 2, '0', STR_PAD_LEFT);
        for ($day = 1; $day <= 7; $day++) {
            array_push($days_of_week, date($format, strtotime($year . "W" . $week_number . $day)));
        }
        return $days_of_week;
    }

    /**
     * Get all the days of a given month
     * @param string $month e.g 1,2,22
     * @param string $year e.g 2012
     * @param string $format . Date format
     * @return array all the dates of a month (yy-mm-dd)
     */
    public static function getDaysOfMonth($month = NULL, $year = NULL, $format = 'Y-m-d')
    {
        if (empty($month))
            $month = date('m');
        if (empty($year))
            $year = date('Y');
        $start_date = $year . '-' . $month . '-01';
        $end_date = $year . '-' . $month . '-' . self::getTotalMonthDays($month, $year);
        return static::generateDateSpan($start_date, $end_date, 1, 'day', $format);
    }

    /**
     * Generate all dates between the given $start_date and $end_date
     * @param string $start_date
     * @param string $end_date
     * @param integer $interval The Interval e.g 1,2,3 etc
     * @param string $interval_type e.g minute,hour,day,month etc
     * @param string $format The date format
     * @return array All the dates from the $start_date to $end_date
     */
    public static function generateDateSpan($start_date = null, $end_date = null, $interval = 1, $interval_type = 'day', $format = 'Y-m-d')
    {
        if (empty($interval))
            $interval = 1;
        // normalize input
        $start_date = is_numeric($start_date) ? $start_date : (is_null($start_date) ? time() : strtotime($start_date));
        $end_date = is_numeric($end_date) ? $end_date : (is_null($end_date) ? strtotime('today') : strtotime($end_date));
        // generate the intervals
        $interval = $interval . ' ' . $interval_type;
        $intervals = [];
        $intervals[] = $next = $start_date;
        do {
            $intervals[] = $next = (is_numeric($interval) ? ($next + $interval) : strtotime($interval, $next));
        } while ($next < $end_date);
        $intervals[] = $end_date;
        // clean and format
        return array_unique(array_map(function ($t) use ($format) {
            return date($format, $t);
        }, $intervals));
    }

    /**
     * Get the total number of days in a given month of a given year
     * @param integer $month
     * @param integer $year
     * @return int
     */
    public static function getTotalMonthDays($month = null, $year = null)
    {
        // return (new Carbon())->parse()->daysInMonth;
        if (empty($month))
            $month = date('m');
        if (empty($year))
            $year = date('Y');
        $month = (int)$month;
        $year = (int)$year;
        if ($month != 2) {
            if ($month == 4 || $month == 6 || $month == 9 || $month == 11)
                return 30;
            else
                return 31;
        } else
            return $year % 4 == "" && $year % 100 != "" ? 29 : 28;
    }

    /**
     * Get all months of a year e.g 2012-01,2012-02 etc
     * @param string $year
     * @param string $delimiter separates the year and month e.g '-','/'
     * @param string $defaultDay
     * @return array containing all the months of a given year e.g 2012/01 or 2012-01
     */
    public static function getYearMonths($year = null, $delimiter = '/', $defaultDay = '01')
    {
        if (empty($year))
            $year = date('Y');
        if (empty($delimiter))
            $delimiter = '/';
        return [
            $year . $delimiter . '01' . $delimiter . $defaultDay,
            $year . $delimiter . '02' . $delimiter . $defaultDay,
            $year . $delimiter . '03' . $delimiter . $defaultDay,
            $year . $delimiter . '04' . $delimiter . $defaultDay,
            $year . $delimiter . '05' . $delimiter . $defaultDay,
            $year . $delimiter . '06' . $delimiter . $defaultDay,
            $year . $delimiter . '07' . $delimiter . $defaultDay,
            $year . $delimiter . '08' . $delimiter . $defaultDay,
            $year . $delimiter . '09' . $delimiter . $defaultDay,
            $year . $delimiter . '10' . $delimiter . $defaultDay,
            $year . $delimiter . '11' . $delimiter . $defaultDay,
            $year . $delimiter . '12' . $delimiter . $defaultDay,
        ];
    }

    /**
     * Get last month given a month and year
     * @param integer $month e.g 1,2,..12,etc
     * @param integer $year e.g 2012
     * @return array e.g  array('month'=>2, 'year'=>2012)
     */
    public static function getPreviousMonth($month = NULL, $year = NULL)
    {
        if (empty($month))
            $month = date('m');
        if (empty($year))
            $year = date('Y');
        if ($month == 1) {
            $lastMonth = 12;
            $year = $year - 1;
        } else {
            $lastMonth = $month - 1;
        }
        return ['month' => $lastMonth, 'year' => $year];
    }

    /**
     * Get next month given a month and year
     * @param integer $month e.g 1,2,..12,etc
     * @param integer $year e.g 2012
     * @return array e.g  array('month'=>2, 'year'=>2012)
     */
    public static function getNextMonth($month = NULL, $year = NULL)
    {
        if (empty($month))
            $month = date('m');
        if (empty($year))
            $year = date('Y');
        if ($month == 12) {
            $nextMonth = 1;
            $year = $year + 1;
        } else {
            $nextMonth = $month + 1;
        }
        return ['month' => $nextMonth, 'year' => $year];
    }

    /**
     * Get last week given a week and year
     * @param integer $week e.g from1,2,..52,etc
     * @param integer $year e.g 2012
     * @return array e.g  array('week'=>2, 'year'=>2012)
     */
    public static function getLastWeek($week = NULL, $year = NULL)
    {
        if (empty($week))
            $week = date('W');
        if (empty($year))
            $year = date('Y');
        if ($week == 1) {
            $lastWeek = 52;
            $year = $year - 1;
        } else {
            $lastWeek = $week - 1;
        }
        return ['week' => $lastWeek, 'year' => $year];
    }

    /**
     * Get next week given a week and year
     * @param integer $week e.g from1,2,..52,etc
     * @param integer $year e.g 2012
     * @return array e.g  array('week'=>2, 'year'=>2012)
     */
    public static function getNextWeek($week = NULL, $year = NULL)
    {
        if (empty($week))
            $week = date('W');
        if (empty($year))
            $year = date('Y');
        if ($week == 52) {
            $nextWeek = 1;
            $year = $year + 1;
        } else {
            $nextWeek = $week + 1;
        }
        return ['week' => $nextWeek, 'year' => $year];
    }

    /**
     * Check whether a given string is a valid date
     * @param string $dateString
     * @return boolean
     */
    public static function checkDateTime($dateString)
    {
        $stamp = strtotime($dateString);
        if (!is_numeric($stamp)) {
            return FALSE;
        }
        $month = date('m', $stamp);
        $day = date('d', $stamp);
        $year = date('Y', $stamp);
        if (checkdate($month, $day, $year)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * get date diff
     * @param string $date1
     * @param string $date2
     * @return \DateInterval $interval->y,$interval->m,$interval->d
     * @throws \Exception
     */
    public static function getDateDiff($date1, $date2)
    {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        return $interval;
    }

    /**
     * The gap in seconds
     *
     * @param int $gap
     * @return array
     */
    public static function getGapInSeconds($gap = 1800)
    {
        $time_arr = [];
        $start = strtotime('12:00am');
        $end = strtotime('11:59pm');
        for ($i = $start; $i <= $end; $i += $gap) {
            $time_arr[date('H:i:s', $i)] = date('g:i a', $i);
        }
        return $time_arr;
    }

    /**
     * check whether a given date is a particular day of week
     * e.g check if date "2012-09-12" is "saturday"
     * @param string $dateString
     * @return boolean
     */
    public static function isDayOfWeek($dateString)
    {
        return Carbon::parse($dateString)->isWeekday();
    }

    /**
     * Get array of years: for select box use
     * @param string $startYear
     * @param bool $forward
     * @param int $maxCount
     * @param bool $prompt
     * @return array
     */
    public static function getYearOptions($startYear = null, $forward = true, $maxCount = 30, $prompt = false)
    {
        $prefix = ['' => '--year--'];
        if (empty($startYear))
            $startYear = date('Y');
        $years = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $years[$startYear] = $startYear;
            if ($forward)
                $startYear++;
            else
                $startYear--;
        }

        return Utils::appendDropDownListPrompt($years, $prompt);
    }

    /**
     * The gap in seconds
     * @param int $gap
     * @return array
     */
    public static function timeOptions($gap = 1800)
    {
        $time_arr = [];
        $start = strtotime('12:00am');
        $end = strtotime('11:59pm');
        for ($i = $start; $i <= $end; $i += $gap) {
            $time_arr[date('H:i:s', $i)] = date('g:i a', $i);
        }
        return $time_arr;
    }

    /**
     * Gets the integer format of a month given a month in string format
     * @param string $stringMonth e.g January
     * @return int e.g 1
     */
    public static function monthToInt($stringMonth)
    {
        $month = date_parse($stringMonth);
        return $month['month'];
    }

    /**
     * Get number of seconds,minutes,hours,days,weeks between two dates
     * @param mixed $date1
     * @param mixed $date2
     * @param string $duration_type either of second,minute,hour,day,week.
     * @return mixed
     * @throws \Exception
     */
    public static function getDurationBetween($date1, $date2, $duration_type = 'day')
    {
        $valid_duration_types = ['second', 'minute', 'hour', 'day', 'week', 'year'];
        if (!in_array($duration_type, $valid_duration_types))
            throw new \InvalidArgumentException('Wrong duration type');

        $d1 = Carbon::parse($date1);

        $d2 = Carbon::parse($date2);

        switch ($duration_type) {
            case 'second':
                return $d1->diffInSeconds($d2);
            case 'minute':
                return $d1->diffInMinutes($d2);
            case 'hour':
                return $d1->diffInHours($d2);
            case 'day':
                return $d1->diffInDays($d2);
            case 'week':
                return $d1->diffInWeeks($d2);
            case 'year':
                return $d1->diffInYears($d2);
            default :
                return FALSE;
        }
    }

    /**
     * @param int $monthNum
     * @return string
     */
    public static function getMonthName($monthNum)
    {
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        return $dateObj->format('F');
    }

    /**
     * get months array
     * @param mixed $prompt
     * @return array
     */
    public static function getMonthOptions($prompt = false)
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {

            $months[$i] = static::getMonthName($i);
        }
        return Utils::appendDropDownListPrompt($months, $prompt);
    }

    /**
     * get date filter params
     * @param string $start_date
     * @param string $end_date
     * @param string $date_field default date_created
     * @param boolean $enforce_defaults
     * @param bool $cast_date
     * <pre>
     * array(
     * "start_date"=>"2014-11-19",
     * "end_date"=>"2014-11-20",
     * "title"=>"2014-11-19 - 2014-11-20",
     * "condition"=>"-----",
     * )
     * </pre>
     * @param null $table_name
     * @return array
     * @throws \Exception
     */
    public static function getDateFilterParams($start_date = null, $end_date = null, $date_field = 'created_at', $enforce_defaults = true, $cast_date = false, $table_name = null)
    {
        $condition = '';
        $title = '';

        if ($enforce_defaults) {
            if (empty($start_date))
                $start_date = static::addDate(date('Y-m-d'), -30, 'day');
            if (empty($end_date))
                $end_date = date('Y-m-d');
        }

        if (!empty($start_date) && !empty($end_date)) {
            $title = static::formatToLocalDate($start_date, 'D dS M y');
            if ($end_date !== $start_date)
                $title .= ' - ' . static::formatToLocalDate($end_date, 'D dS M y');
            if ($cast_date)
                $date_field = !empty($table_name) ? 'DATE(' . $table_name . '.[[' . $date_field . ']])' : 'DATE([[' . $date_field . ']])';
            else
                $date_field = !empty($table_name) ? $table_name . '.[[' . $date_field . ']]' : '[[' . $date_field . ']]';
            $condition .= $date_field . '>=' . Yii::$app->db->quoteValue($start_date) . ' AND ' . $date_field . '<=' . Yii::$app->db->quoteValue($end_date);
        }

        return [
            'from' => $start_date,
            'to' => $end_date,
            'title' => $title,
            'condition' => $condition,
        ];
    }

    /**
     * @return array
     */
    public static function reportsDurationOptions()
    {
        return [
            self::REPORTS_DURATION_DAILY => Lang::t('DAILY'),
            self::REPORTS_DURATION_WEEKLY => Lang::t('WEEKLY'),
            self::REPORTS_DURATION_MONTHLY => Lang::t('MONTHLY'),
            self::REPORTS_DURATION_YEARLY => Lang::t('YEARLY'),
        ];
    }

    /**
     * This function returns today's date expressed in the format given. The date returned is converted to the local timezone
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public static function getToday($format = 'Y-m-d')
    {
        $today = date('Y-m-d H:i:s');
        return static::formatToLocalDate($today, $format);
    }


    /**
     * @param string|null $dateString
     * @param string|null $format
     * @return false|string|null
     */
    public static function getLastDayOfMonth($dateString = null, $format = null)
    {
        $lastDay = null;
        if (empty($dateString)) {
            //Last day of current month.
            $lastDay = date("Y-m-t");
        } else {
            $lastDay = date("Y-m-t", strtotime($dateString));
        }
        if (!empty($format)) {
            return static::formatDate($lastDay, $format);
        }
        return $lastDay;
    }
}
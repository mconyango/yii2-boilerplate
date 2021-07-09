<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/06
 * Time: 4:44 PM
 */

namespace common\helpers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\db\Connection;

class DbUtils
{
    //DB DRIVERS
    const DRIVER_MYSQL = 'mysql';
    const DRIVER_POSTGRES = 'pgsql';

    /**
     * @param string $column the table column name
     * @param string $value the column value
     * @param string|array $condition the current condition
     * @param array $params the current params
     * @param string $conditionConnector
     * @param string $operator
     * @return array
     * @throws \Exception
     */
    public static function appendCondition($column, $value, $condition = '', $params = [], $conditionConnector = 'AND', $operator = '=')
    {
        $paramKey = $column;
        if (!is_array($condition)) {
            if (strtolower($operator) === 'like') {
                $value = '%' . $value . '%';
            }
            if (!empty($condition))
                $condition .= ' ' . $conditionConnector . ' ';
            $i = rand(1, 100);
            $escapedColumn = $column;
            if (!strpos($column, '(')) {
                $escapedColumn = '[[' . $column . ']]';
            } else {
                $paramKey = random_int(1, 10000000);
            }
            if ($value === null && ($operator === '=' || $operator === '<>' || $operator === '!=')) {
                if ($operator === '=') {
                    $condition .= $escapedColumn . ' IS NULL';
                } else {
                    $condition .= $escapedColumn . ' IS NOT NULL';
                }
            } else {
                $condition .= $escapedColumn . ' ' . $operator . ' :mc' . $paramKey . $i;
                $params[':mc' . $paramKey . $i] = $value;
            }

        } else {
            if ($operator === '=') {
                $condition[$column] = $value;
            } elseif ($operator === '<>' || $operator === '!=' || $operator === 'like') {
                throw new InvalidArgumentException('Condition must be a string, array passed');
            }
        }

        return [$condition, $params];
    }

    /**
     * @param $date_field
     * @param Connection $db
     * @return string
     */
    public static function castDATE($date_field, $db = null)
    {
        $default = 'DATE([[' . $date_field . ']])';
        if (is_null($db))
            $db = Yii::$app->db;

        switch ($db->getDriverName()) {
            case self::DRIVER_MYSQL:
                return $default;
                break;
            case self::DRIVER_POSTGRES:
                return $default;
                break;
            default:
                return $default;

        }
    }

    public static function castMONTH($date_field, $db = null)
    {
        $default = 'MONTH([[' . $date_field . ']])';
        if (is_null($db))
            $db = Yii::$app->db;

        switch ($db->getDriverName()) {
            case self::DRIVER_MYSQL:
                return $default;
                break;
            case self::DRIVER_POSTGRES:
                return 'extract(month from [[' . $date_field . ']])';
                break;
            default:
                return $default;

        }
    }

    /**
     * @param $date_field
     * @param Connection $db
     * @return string
     */
    public static function castYEAR($date_field, $db = null)
    {
        $default = 'YEAR([[' . $date_field . ']])';
        if (is_null($db))
            $db = Yii::$app->db;

        switch ($db->getDriverName()) {
            case self::DRIVER_MYSQL:
                return $default;
                break;
            case self::DRIVER_POSTGRES:
                return 'extract(year from [[' . $date_field . ']])';
                break;
            default:
                return $default;

        }
    }

    /**
     * @param $dateField
     * @param $dateValue
     * @param Connection $db
     * @param string $condition
     * @param array $params
     * @return array
     * @throws NotSupportedException
     */
    public static function YEARWEEKCondition($dateField, $dateValue, $db = null, $condition = '', $params = [])
    {
        if (is_null($db))
            $db = Yii::$app->db;
        if (!empty($condition))
            $condition .= ' AND ';


        switch ($db->getDriverName()) {
            case self::DRIVER_MYSQL:
                $condition .= 'YEARWEEK([[' . $dateField . ']],1)=YEARWEEK(:' . $dateField . ',1)';
                $params[':' . $dateField] = $dateValue;
                break;
            case self::DRIVER_POSTGRES:
                $condition .= 'to_char([[' . $dateField . ']],\'IYYY_IW\')=to_char(:' . $dateField . '::date,\'IYYY_IW\')';
                $params[':' . $dateField] = $dateValue;
                break;
            default:
                throw new NotSupportedException('MSSQL is not supported yet.');
        }

        return [$condition, $params];
    }

    /**
     * get IN condition and params
     * @param $column
     * @param $values
     * @param mixed $condition
     * @param array $params
     * @param string $operator IN | NOT IN
     * @return array
     */
    public static function appendInCondition($column, $values, $condition = '', $params = [], $operator = 'IN')
    {
        if (is_array($condition)) {
            $condition[$column] = $values;
            return [$condition, $params];
        }

        if (!empty($condition))
            $condition .= ' AND ';
        $param_count = 0;
        $param_prefix = ':mc_' . $column;
        if (($n = count($values)) < 1)
            $condition .= '0=1';
        // 0=1 is used because in MSSQL value alone can't be used in WHERE
        elseif ($n === 1) {
            $value = reset($values);
            if ($value === null)
                $condition .= '[[' . $column . ']]' . ' IS NULL';
            else {
                $operator = strtolower($operator) === 'in' ? '=' : '<>';
                $condition .= '[[' . $column . ']]' . $operator . $param_prefix . $param_count;
                $params[$param_prefix . $param_count] = $value;
            }
        } else {
            $in = [];
            foreach ($values as $value) {
                $in[] = $param_prefix . $param_count;
                $params[$param_prefix . $param_count++] = $value;
            }
            $condition .= '[[' . $column . ']]' . ' ' . $operator . ' (' . implode(', ', $in) . ')';
        }

        return [$condition, $params];
    }
}

<?php

namespace common\models;

use backend\modules\auth\models\Users;
use backend\modules\auth\models\AuditTrail;
use common\helpers\DateUtils;
use common\helpers\DbUtils;
use common\helpers\Utils;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord as AR;
use yii\db\Connection;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * ActiveRecord is the customized base activeRecord class.
 * All Model classes for this application should extend from this base class.
 * @author Fred <mconyango@gmail.com>
 * Created on 2015-11-17
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property int $is_deleted
 * @property string $deleted_at
 * @property int $deleted_by
 *
 * @property Users $createdByUser
 * @property Users $updatedByUser
 */
abstract class ActiveRecord extends AR
{
    use ControllerActionTrait, ReportsTrait;

    //used by getStats() function
    const STATS_TODAY = '1';
    const STATS_THIS_WEEK = '2';
    const STATS_LAST_WEEK = '3';
    const STATS_THIS_MONTH = '4';
    const STATS_LAST_MONTH = '5';
    const STATS_THIS_YEAR = '6';
    const STATS_LAST_YEAR = '7';
    const STATS_ALL_TIME = '8';
    const STATS_DATE_RANGE = '9';
    //special constants
    const SCENARIO_SEARCH = 'search';
    const SCENARIO_INSERT = 'insert';
    const SCENARIO_UPDATE = 'update';
    const SEARCH_FIELD = '_searchField';

    //audit trail
    /**
     * @var string
     */
    public $actionCreateDescriptionTemplate = 'Created a resource. Table affected: {{table}}, Record modified:{{id}}';
    /**
     * @var string
     */
    public $actionUpdateDescriptionTemplate = 'Updated a resource. Table affected: {{table}}, Record modified:{{id}}';
    /**
     * @var string
     */
    public $actionDeleteDescriptionTemplate = 'Deleted a resource. Table affected: {{table}}, Record modified:{{id}}';
    /**
     * @var bool
     */
    public $enableAuditTrail = true;
    /**
     * @var array
     */
    public $changedAttributes;

    /**
     * @var array
     */
    public $oldDbAttributes;
    /**
     * Set empty values to NULL b4 save
     * @var boolean
     */
    public $setEmptyValuesNullBeforeSave = true;

    /**
     * This property is user by \backend\modules\conf\models\FormSelectionCache to auto cache selected fields to improve
     * usability when filling drop down forms by pre-filling with what user selected last
     * This is implemented at the model level. Set this property to true at the model level to activate this functionality
     * @var bool
     */
    public $cacheSelectedOptions = false;

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     *
     * ~~~
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ~~~
     *
     * Note that a behavior class must extend from [[Behavior]]. Behavior names can be strings
     * or integers. If the former, they uniquely identify the behaviors. If the latter, the corresponding
     * behaviors are anonymous and their properties and methods will NOT be made available via the component
     * (however, the behaviors can still respond to the component's events).
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     *
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        $query = parent::find();
        $class = get_called_class();
        /* @var $object ActiveRecord */
        $object = (new $class());
        /*if ($object->hasAttribute('is_deleted')) {
            $query->andFilterWhere([$object->tableName() . '.[[is_deleted]]' => 0]);
        }*/

        return $query;
    }

    /**
     * Get short classname (without the namespace)
     * @return string
     * @throws \ReflectionException
     */
    public static function shortClassName()
    {
        $reflect = new \ReflectionClass(static::class);
        return $reflect->getShortName();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {

            if ($this->setEmptyValuesNullBeforeSave) {
                $this->setEmptyValuesNull();
            }

            // insertion operation, when insert===true. otherwise, its an update operation
            if ($insert) {
                //created_by
                if (Utils::isWebApp() && $this->hasAttribute('created_by') && !Yii::$app->user->isGuest) {
                    $this->created_by = Yii::$app->user->id;
                }
                //created_at
                if ($this->hasAttribute('created_at')) {
                    $this->created_at = DateUtils::mysqlTimestamp();
                }
                // update operation
                if ($this->hasAttribute('updated_at')) {
                    $this->updated_at = DateUtils::mysqlTimestamp();
                }
            } else {
                //updated_by
                if (Utils::isWebApp() && $this->hasAttribute('updated_by') && !Yii::$app->user->isGuest) {
                    $this->updated_by = Yii::$app->user->id;
                }
                // update operation
                if ($this->hasAttribute('updated_at')) {
                    $this->updated_at = DateUtils::mysqlTimestamp();
                }
            }

            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->enableAuditTrail) {
            $action = $insert ? AuditTrail::ACTION_CREATE : AuditTrail::ACTION_UPDATE;
            AuditTrail::addAuditTrail($this, $action);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        if ($this->enableAuditTrail) {
            AuditTrail::addAuditTrail($this, AuditTrail::ACTION_DELETE);
        }
        parent::afterDelete();
    }

    public function afterFind()
    {
        $this->oldDbAttributes = $this->attributes;
        parent::afterFind();
    }


    /**
     * Load model function. With an option to throw the 404 exception
     * @param mixed $condition , or primary key value
     * @param bool $throwException
     * @return $this $model
     * @throws NotFoundHttpException
     */
    public static function loadModel($condition, $throwException = true)
    {
        $model = static::findOne($condition);
        if ($model === null) {

            if ($throwException)
                throw new NotFoundHttpException('The requested resource was not found.');
        }
        return $model;
    }

    /**
     * Get a scalar value from the table
     * @param string|array $column
     * @param string|array $condition
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public static function getScalar($column, $condition = '', $params = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->select($column)
            ->from(static::tableName())
            ->where($condition, $params)
            ->limit(1)
            ->scalar(static::getDb());
    }

    /**
     * Get a row of a table
     * @param string|array $columns
     * @param string|array $condition
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public static function getOneRow($columns = '*', $condition = '', $params = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->select($columns)
            ->from(static::tableName())
            ->where($condition, $params)
            ->limit(1)
            ->one(static::getDb());
    }

    /**
     * get column data of a table
     * @param string|array $column
     * @param string|array $condition
     * @param array $params
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public static function getColumnData($column, $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->select($column)
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['orderBy']))
            $command->orderBy($options['orderBy']);
        if (!empty($options['limit']))
            $command->limit($options['limit']);
        if (!empty($options['offset']))
            $command->offset($options['offset']);

        return $command->column(static::getDb());
    }

    /**
     * @param string $valueColumn
     * @param string $keyColumn
     * @return array
     */
    public static function getIndexedColumnData($valueColumn, $keyColumn)
    {
        return static::find()->select([$valueColumn])->indexBy($keyColumn)->column();
    }

    /**
     * Gets a particular field of a table
     * @param mixed $id The Primary Key of the model table
     * @param string $column The field to be returned
     * @return boolean Returns the field if found else returns false
     * @throws \Exception
     */
    public static function getFieldByPk($id, $column)
    {
        if (empty($id))
            return null;

        $primary_key = static::getPrimaryKeyColumn();

        return static::getScalar($column, [$primary_key => $id]);
    }

    /**
     * Get the row counts
     * @param string|array $condition
     * @param array $params
     * @return int $count
     * @throws \Exception
     */
    public static function getCount($condition = '', $params = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return (new Query())
            ->from(static::tableName())
            ->where($condition, $params)
            ->count('*', static::getDb());
    }

    /**
     * Get the sum of rows
     * @param string $column Field to be summed
     * @param string|array $condition
     * @param array $params
     * @param array $options
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @return double
     * @throws \Exception
     */
    public static function getSum($column, $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['groupBy']))
            $command->groupBy($options['groupBy']);
        return $command->sum($column, static::getDb());
    }

    /**
     * Get rowset using the query builder e.g Yii::app()->db->createCommand()
     * @param mixed $columns
     * @param mixed $condition
     * @param array $params
     * @param array $options
     * <pre>
     *  array(
     *    "orderBy" => ['id' => SORT_ASC, 'name' => SORT_DESC],
     *    "limit" => 20,
     *    "offset" => null,
     *    "groupBy" => ['id','name'],
     * )
     * </pre>
     * @return array $data
     * @throws \Exception
     */
    public static function getData($columns = ['*'], $condition = '', $params = [], $options = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        $command = (new Query())
            ->select($columns)
            ->from(static::tableName())
            ->where($condition, $params);
        if (!empty($options['orderBy']))
            $command->orderBy($options['orderBy']);
        if (!empty($options['groupBy']))
            $command->groupBy($options['groupBy']);
        if (!empty($options['limit']))
            $command->limit($options['limit']);
        if (!empty($options['offset']))
            $command->offset($options['offset']);
        return $command->all(static::getDb());
    }

    /**
     * @param array|string $condition
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    public static function exists($condition = '', $params = [])
    {
        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_deleted' => 0]);
        return static::find()
            ->where($condition, $params)
            ->exists(static::getDb());
    }

    /**
     * Gets the primary key column name of a table
     * @return string primary key column name
     */
    public static function getPrimaryKeyColumn()
    {
        $primaryKey = static::primaryKey();
        return $primaryKey[0];
    }

    /**
     * composes drop-down list data from a model using Html::listData function
     * @param string $valueColumn
     * @param string $textColumn
     * @param boolean $prompt
     * @param string $condition
     * @param array $params
     * @param array $options
     *
     *  <pre>
     *   array(
     *    "orderBy"=>""//String,
     *    "groupField"=>null//String could be anonymous function that gets the group field
     *    "extraColumns"=>[]// array : you must pass at least the grouping field if groupField is an anonymous function
     * )
     * </pre>
     *
     * @return array
     * @throws \Exception
     * @see CHtml::listData();
     */
    public static function getListData($valueColumn = 'id', $textColumn = 'name', $prompt = false, $condition = '', $params = [], $options = [])
    {
        $valueFieldAlias = 'id';
        $textFieldAlias = 'name';
        $columns = [
            "[[{$valueColumn}]] as [[{$valueFieldAlias}]]",
            !strpos($textColumn, '(') ? "[[{$textColumn}]] as [[{$textFieldAlias}]]" : "{$textColumn} as [[{$textFieldAlias}]]",
        ];
        if (!empty($options['extraColumns'])) {
            foreach ($options['extraColumns'] as $c) {
                $columns[] = "[[{$c}]]";
            }

        }
        if (empty($options['orderBy'])) {
            $options['orderBy'] = ArrayHelper::getValue($options, 'orderBy', $textFieldAlias);
        }

        list($condition, $params) = static::appendDefaultConditions($condition, $params, ['is_active' => 1, 'is_deleted' => 0]);

        $data = static::getData($columns, $condition, $params, $options);
        if ($prompt !== false && null !== $prompt) {
            $prompt = $prompt === true ? "[select one]" : $prompt;
            $first_row = [$valueFieldAlias => "", $textFieldAlias => $prompt];
            if (!empty($options['extraColumns'])) {
                foreach ($options['extraColumns'] as $column) {
                    $first_row[$column] = '';
                }
            }

            $data = array_merge([$first_row], $data);
        }

        $groupField = ArrayHelper::getValue($options, 'groupField', null);

        return ArrayHelper::map($data, $valueFieldAlias, $textFieldAlias, $groupField);
    }

    /**
     * Gets the next Integer  ID for non-auto_increment integer keys
     * @param string $column the id column. default is the primary key column
     * @param int $start_from start ID
     * @return int the next integer id
     */
    public static function getNextIntegerID($column = NULL, $start_from = 0)
    {
        if (empty($column))
            $column = static::getPrimaryKeyColumn();

        $max_id = (new Query())
            ->from(static::tableName())
            ->max('[[' . $column . ']]', static::getDb());

        if (empty($max_id))
            $max_id = $start_from;
        return $max_id + 1;
    }

    /**
     * Insert multiple records to the db
     * Note: No validation done here
     * ```php
     *   $rows = array(
     *   array('tom',30),
     *   array('Fred',28),
     * );
     * ```
     * @param array $rows
     * @param string|null $table
     * @param Connection $db
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function insertMultiple($rows, $table = null, $db = null)
    {
        if (empty($rows))
            return false;
        if (empty($table))
            $table = static::tableName();
        $columns = array_keys(current($rows));
        if (null === $db) {
            $db = Yii::$app->db;
        }
        return $db->createCommand()->batchInsert($table, $columns, $rows)->execute();
    }

    /**
     * Set empty values to NULL b4 insert/update
     */
    protected function setEmptyValuesNull()
    {
        foreach ($this->attributes as $k => $v) {
            if (is_string($v) && trim($v) === "") {
                $this->{$k} = null;
            }
        }
    }

    /**
     * @param $id
     * @param string|null $primaryKeyField
     * @param bool $permanent
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public static function softDelete($id, $primaryKeyField = null, $permanent = false)
    {
        if (YII_ENV === 'dev') {
            $permanent = true;
        }
        $field = 'is_deleted';
        $value = 1;
        if (!empty($primaryKeyField))
            $model = static::loadModel([$primaryKeyField => $id], false);
        else
            $model = static::loadModel($id, false);
        if ($model === null)
            return false;

        if ($permanent || !$model->hasAttribute($field)) {
            return $model->delete();
        } else {
            $attributes = [
                $field => $value,
            ];

            if ($model->hasAttribute('deleted_at'))
                $attributes['deleted_at'] = DateUtils::mysqlTimestamp();
            if ($model->hasAttribute('deleted_by'))
                $attributes['deleted_by'] = Yii::$app->user->id;

            if ($model->updateAll($attributes, [static::getPrimaryKeyColumn() => $id])) {
                $model->afterDelete();
                return true;
            }
            return false;
        }
    }

    /**
     * @param string $condition
     * @param array $params
     * @param array $columns
     * @return array
     * @throws \Exception
     */
    public static function appendDefaultConditions($condition = '', $params = [], $columns = ['is_deleted' => 0])
    {
        /* @var $model ActiveRecord */
        $class_name = static::class;
        $model = new $class_name();
        foreach ($columns as $k => $v) {
            if ($model->hasAttribute($k)) {
                list($condition, $params) = DbUtils::appendCondition($k, $v, $condition, $params);
            }
        }
        return [$condition, $params];
    }

    /**
     * Get stats
     * @param string $durationType e.g today,this_week,this_month,this_year, defaults to null (all time)
     * @param string $condition
     * @param array $params
     * @param mixed $sum if false then the count is return else returns the sum of the $sum field: defaults to FALSE
     * @param string $dateField The date field of the table to be queried for duration stats. defaults to "date_created"
     * @param string $from date_range from
     * @param string $to date_range to
     *
     * @return integer count or sum
     * @throws \Exception
     * @throws \yii\base\NotSupportedException
     */
    public static function getStats($durationType, $condition = '', $params = [], $sum = false, $dateField = 'created_at', $from = null, $to = null)
    {
        $today = DateUtils::formatToLocalDate(date('Y-m-d H:i:s', time()), 'Y-m-d');
        $timezone = 'UTC';
        $this_month = DateUtils::formatDate($today, 'm', $timezone);
        $this_year = DateUtils::formatDate($today, 'Y', $timezone);

        switch ($durationType) {
            case self::STATS_TODAY:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castDATE($dateField, static::getDb()), $today, $condition, $params);
                break;
            case self::STATS_THIS_WEEK:
                list($condition, $params) = DbUtils::YEARWEEKCondition($dateField, $today, static::getDb(), $condition, $params);
                break;
            case self::STATS_LAST_WEEK:
                $date = DateUtils::addDate($today, '-7', 'day');
                list($condition, $params) = DbUtils::YEARWEEKCondition($dateField, $date, static::getDb(), $condition, $params);
                break;
            case self::STATS_THIS_MONTH:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $this_year, $condition, $params);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castMONTH($dateField, static::getDb()), $this_month, $condition, $params);
                break;
            case self::STATS_LAST_MONTH:
                $date = DateUtils::addDate($today, '-1', 'month');
                $year = DateUtils::formatDate($date, 'Y', $timezone);
                $month = DateUtils::formatDate($date, 'm', $timezone);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $year, $condition, $params);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castMONTH($dateField, static::getDb()), $month, $condition, $params);
                break;
            case self::STATS_THIS_YEAR:
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $this_year, $condition, $params);
                break;
            case self::STATS_LAST_YEAR:
                $year = DateUtils::formatDate(DateUtils::addDate($today, '-1', 'year'), 'Y', $timezone);
                list($condition, $params) = DbUtils::appendCondition(DbUtils::castYEAR($dateField, static::getDb()), $year, $condition, $params);
                break;
        }

        if (!empty($from) && !empty($to)) {
            if (!empty($condition))
                $condition .= ' AND ';
            $casted_date = DbUtils::castDATE($dateField, static::getDb());
            $condition .= $casted_date . '>=:from AND ' . $casted_date . '<=:to)';
            $params[':from'] = $from;
            $params[':to'] = $to;
        }

        if ($sum)
            return static::getSum($sum, $condition, $params);
        else
            return static::getCount($condition, $params);
    }

    /**
     * Sets default column values as defined in the db
     * (Yii2 dropped support for setting model attributes as per the defaults set in the db)
     * @param array $columns key=>value where key is the column name and the value is the column value.
     */
    public function setDefaults($columns)
    {
        if ($this->getScenario() !== self::SCENARIO_SEARCH) {
            foreach ($columns as $k => $v) {
                if ($this->hasAttribute($k) && is_null($this->{$k}))
                    $this->{$k} = $v;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information or unnecessary information
        //is_deleted,deleted_at,deleted_by

        $excluded_fields = [
            'is_deleted',
            'deleted_at',
            'deleted_by',
        ];

        foreach ($excluded_fields as $f) {
            if ($this->hasAttribute($f)) {
                unset($fields[$f]);
            }
        }

        return $fields;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getGridViewWidgetId()
    {
        return $this->shortClassName() . '-grid';
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getPjaxWidgetId()
    {
        $gridId = $this->getGridViewWidgetId();
        return $gridId . '-pjax';
    }

    /**
     * Get attributes that have been modified
     * @return array
     */
    public function getChangedAttributes()
    {
        $changed = [];
        foreach ($this->safeAttributes() as $k) {
            if (isset($this->oldDbAttributes[$k]) && $this->oldDbAttributes[$k] != $this->attributes[$k]) {
                $changed[$k] = ['old' => $this->oldDbAttributes[$k], 'new' => $this->attributes[$k]];
            }
        }

        return $changed;
    }

    public static function getCleanTableName()
    {
        $prefix = '{{%';
        $suffix = '}}';

        return preg_replace("/^{$prefix}|{$suffix}$/", "", static::tableName());
    }

    /**
     *
     * @param string $relation
     * @param string $attribute
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getRelationAttributeValue($relation, $attribute, $defaultValue = null)
    {
        $relations = explode('.', $relation);
        $depth = count($relations);
        $model = null;
        if ($depth > 1) {
            for ($i = 0; $i < $depth; $i++) {
                $r = $relations[$i];
                if ($model === null) {
                    $model = $this->{$r};
                } else {
                    $model = $model->{$r};
                }

                if ($model === null) {
                    return $defaultValue;
                }
            }

            return null !== $model ? $model->{$attribute} : $defaultValue;
        }

        return null !== $this->{$relation} ? $this->{$relation}->{$attribute} : $defaultValue;
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedByUser()
    {
        return $this->hasOne(Users::class, ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getUpdatedByUser()
    {
        return $this->hasOne(Users::class, ['id' => 'updated_by']);
    }

    /**
     * @param string $attribute
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttributeSchemaType($attribute){
        $column = static::getTableSchema()->getColumn($attribute);
        if ($column !== null){
            return $column->dbType;
        }
        return null;
    }

}

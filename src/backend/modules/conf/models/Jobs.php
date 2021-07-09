<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "conf_jobs".
 *
 * @property string $id
 * @property string $last_run
 * @property integer $execution_type
 * @property integer $is_active
 * @property integer $threads
 * @property integer $max_threads
 * @property integer $sleep
 * @property string $start_time
 * @property string $end_time
 */
class Jobs extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;
    //A job times out if last run in seconds or more
    const TIMEOUT_SECS = 300;
    //execution types
    const EXEC_TYPE_CRON = 1;
    const EXEC_TYPE_DAEMON = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_jobs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['last_run', 'start_time', 'end_time'], 'safe'],
            [['execution_type', 'is_active', 'threads', 'max_threads', 'sleep'], 'integer'],
            [['id'], 'string', 'max' => 30],
            [['id'], 'unique'],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Job ID',
            'last_run' => 'Last Run',
            'is_active' => 'Active',
            'threads' => 'Processes',
            'max_threads' => 'Max Processes',
            'sleep' => 'Sleep (in Sec.)',
            'execution_type' => 'Type',
            'start_time' => 'From',
            'end_time' => 'To',
        ];
    }

    /**
     * This method is called when the AR object is created and populated with the query result.
     * The default implementation will trigger an [[EVENT_AFTER_FIND]] event.
     * When overriding this method, make sure you call the parent implementation to ensure the
     * event is triggered.
     */
    public function afterFind()
    {
        $this->threads = JobProcesses::getTotalProcesses($this->id);
        parent::afterFind();
    }

    /**
     * Sets that status of the job as active so that it executes when the cron job runs
     * @param string $job_id
     */
    public static function startJob($job_id)
    {
        Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_active' => 1], ['id' => $job_id])
            ->execute();
    }

    /**
     * Stop a job from executing
     * @param string $job_id
     */
    public static function stopJob($job_id)
    {
        $updated = Yii::$app->db->createCommand()
            ->update(static::tableName(), ['is_active' => 0, 'threads' => 0, 'last_run' => null], ['id' => $job_id])
            ->execute();
        if ($updated) {
            JobProcesses::deleteAll(['job_id' => $job_id]);
        }
    }

    /**
     * Check whether a job has timed out
     * @param string $job_id
     * @return boolean
     */
    public static function hasTimedOut($job_id)
    {
        $sql = 'SELECT (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP([[last_run]])) as [[last_run]],[[sleep]] FROM ' . static::tableName() . ' WHERE [[id]]=:id';
        $row = Yii::$app->db->createCommand($sql, [':id' => $job_id])
            ->queryOne();
        if (empty($row))
            return TRUE;
        if ((int)$row['last_run'] >= (self::TIMEOUT_SECS + (int)$row['sleep'])) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @param string $job_id
     */
    public static function updateLastRun($job_id)
    {
        Yii::$app->db->createCommand()
            ->update(static::tableName(), ['last_run' => new Expression('NOW()')], ['id' => $job_id])
            ->execute();
    }

    /**
     *
     * @return array
     */
    public static function executionTypeOptions()
    {
        return [
            self::EXEC_TYPE_CRON => static::decodeExecutionType(self::EXEC_TYPE_CRON),
            self::EXEC_TYPE_DAEMON => static::decodeExecutionType(self::EXEC_TYPE_DAEMON),
        ];
    }

    /**
     *
     * @param string $exec_type
     * @return array
     */
    public static function decodeExecutionType($exec_type)
    {
        $decoded = "";
        switch ($exec_type) {
            case self::EXEC_TYPE_CRON:
                $decoded = Lang::t('Cron Job');
                break;
            case self::EXEC_TYPE_DAEMON:
                $decoded = Lang::t('Daemon');
                break;
        }

        return $decoded;
    }


    /**
     * Search params for the active search
     * ```php
     *   return [
     *       ["name","_searchField","AND|OR"],//default is AND only include this param if there is a need for OR condition
     *       'id',
     *       'email'
     *   ];
     * ```
     * @return array
     */
    public function searchParams()
    {
        return [
            'is_active',
        ];
    }
}

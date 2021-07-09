<?php

namespace backend\modules\conf\models;

use common\helpers\Lang;
use common\helpers\Utils;
use common\models\ActiveRecord;
use common\models\ActiveSearchInterface;
use common\models\ActiveSearchTrait;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "conf_job_processes".
 *
 * @property string $id
 * @property string $job_id
 * @property string $last_run_datetime
 * @property integer $status
 * @property string $created_at
 */
class JobProcesses extends ActiveRecord implements ActiveSearchInterface
{
    use ActiveSearchTrait;

    const STATUS_RUNNING = '1';
    const STATUS_SLEEPING = '2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%conf_job_processes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'job_id'], 'required'],
            [['last_run_datetime'], 'safe'],
            [['status'], 'integer'],
            [['id'], 'string', 'max' => 128],
            [['job_id'], 'string', 'max' => 30],
            [[self::SEARCH_FIELD], 'safe', 'on' => self::SCENARIO_SEARCH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Lang::t('Process ID'),
            'job_id' => Lang::t('Job'),
            'last_run_datetime' => Lang::t('Last Run'),
            'status' => Lang::t('Status'),
            'created_at' => Lang::t('Date Started'),
        ];
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
            'id',
            'job_id',
            'status',
        ];
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function decodeStatus($value)
    {
        $decoded = '';
        switch ($value) {
            case self::STATUS_RUNNING:
                $decoded = Lang::t('Running');
                break;
            case self::STATUS_SLEEPING:
                $decoded = Lang::t('Sleeping');
                break;
        }

        return $decoded;
    }

    /**
     * Create a new process
     * @param Jobs $job
     * @return string $process_id
     */
    public static function createProcess($job)
    {
        $process_id = Utils::uuid();
        $now = new Expression('NOW()');

        Yii::$app->db->createCommand()
            ->insert(static::tableName(), [
                'id' => $process_id,
                'job_id' => $job->id,
                'last_run_datetime' => $now,
                'status' => self::STATUS_RUNNING,
                'created_at' => $now,
            ])->execute();

        $job->threads = static::getTotalProcesses($job->id);
        $job->save(false);

        return $process_id;
    }

    /**
     * Clear processes of a job
     * @param string $job_id
     * @param boolean $check_expiry
     */
    public static function clearProcesses($job_id, $check_expiry = true)
    {
        $expiry = 30; //expire all processes which have stalled for 30mins
        $conditions = '[[job_id]]=:job_id';
        $params = [':job_id' => $job_id];
        if ($check_expiry) {
            $conditions .= ' AND ([[last_run_datetime]] < DATE_SUB(NOW(), INTERVAL :expiry MINUTE))';
            $params[':expiry'] = $expiry;
        }


        if (static::deleteAll($conditions, $params)) {
            Yii::$app->db->createCommand()
                ->update(Jobs::tableName(), ['threads' => static::getTotalProcesses($job_id)], ['id' => $job_id])
                ->execute();
        }
    }

    /**
     * Get total processes of a job
     * @param string $job_id
     * @return string
     */
    public static function getTotalProcesses($job_id)
    {
        return static::getCount(['job_id' => $job_id]);
    }

    /**
     *
     * @param string $job_id
     * @return string
     */
    public static function getTotalRunning($job_id)
    {
        return static::getCount(['job_id' => $job_id, 'status' => self::STATUS_RUNNING]);
    }

    /**
     *
     * @param string $job_id
     * @return string
     */
    public static function getTotalSleeping($job_id)
    {
        return static::getCount(['job_id' => $job_id, 'status' => self::STATUS_SLEEPING]);
    }

    /**
     * Retire a process
     * @param integer $job_id
     * @param integer $process_id
     * @param integer $expiry_minutes
     * @return bool
     */
    public static function retireProcess($job_id, $process_id, $expiry_minutes = 30)
    {
        $conditions = '[[id]]=:id AND ([[created_at]] < DATE_SUB(NOW(), INTERVAL :expiry MINUTE))';
        $params = [':expiry' => $expiry_minutes, ':id' => $process_id];

        if (static::deleteAll($conditions, $params)) {
            Yii::$app->db->createCommand()
                ->update(Jobs::tableName(), ['threads' => static::getTotalProcesses($job_id)], ['id' => $job_id])
                ->execute();

            return true;
        }

        return false;
    }
}

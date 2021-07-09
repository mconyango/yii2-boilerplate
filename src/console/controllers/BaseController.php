<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/07
 * Time: 4:52 PM
 */

namespace console\controllers;

use common\helpers\DateUtils;
use Yii;
use backend\modules\conf\models\Jobs;
use backend\modules\conf\models\JobProcesses;
use yii\console\Controller;
use yii\console\Exception;
use yii\db\Expression;
use yii\helpers\Console;

class BaseController extends Controller
{
    /**
     * Start a Job
     * @param string $job_id
     * @return bool|void
     * @throws \Exception
     */
    protected function startJob($job_id)
    {
        try {
            /* @var $job Jobs */
            $job = Jobs::findOne($job_id);
            if (null === $job)
                throw new Exception("{$job_id} does not exist.");
            if (!$job->is_active)
                return false;

            //check start time and end time
            if (!empty($job->start_time) && !empty($job->end_time)) {
                $time_now = time();
                if (!($time_now >= strtotime($job->start_time) && $time_now <= strtotime($job->end_time))) {
                    JobProcesses::clearProcesses($job->id, false);
                    return false;
                }
            }
            //non continuous execution type
            if ($job->execution_type === Jobs::EXEC_TYPE_CRON) {
                $this->{$job_id}();
                return Jobs::updateLastRun($job->id);
            }

            JobProcesses::clearProcesses($job->id);
            $job->refresh();
            if ($job->threads >= $job->max_threads) {
                Yii::$app->end();
            }
            //create process
            $process_id = JobProcesses::createProcess($job);
            while ($job->is_active) {
                $job = $this->runJob($job, $process_id);
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage() . '\n' . $e->getTraceAsString());
        }
    }

    /**
     * @param Jobs $job
     * @param integer $process_id
     * @return mixed
     * @throws \yii\base\ExitException
     * @throws \yii\db\Exception
     */
    protected function runJob($job, $process_id)
    {
        try {
            if (!Yii::$app->db->getIsActive()) {
                Yii::$app->db->open(); //open new connection
            }

            if ($job->threads >= $job->max_threads) {
                if (JobProcesses::retireProcess($job->id, $process_id, $job->max_threads + 1))
                    Yii::$app->end();
            }

            /* @var $process JobProcesses */
            $process = JobProcesses::findOne($process_id);
            if (null === $process) {
                Yii::$app->end();
            }

            //update last run
            $process->last_run_datetime = new Expression('NOW()');
            $process->status = JobProcesses::STATUS_RUNNING;
            $process->save(false);

            $this->{$job->id}();

            Jobs::updateLastRun($job->id);

            $job->refresh();

            $this->sleep($job, $process);
        } catch (Exception $e) {
            //delete the process
            Yii::$app->db->createCommand()
                ->delete(JobProcesses::tableName(), ['id' => $process_id])
                ->execute();

            //reduce the threads by one
            $job->threads = $job->threads - 1;
            $job->save(false);

            Yii::error($e->getMessage());
        }

        return $job;
    }

    /**
     *
     * @param Jobs $job
     * @param JobProcesses $process
     */
    protected function sleep($job, $process)
    {
        $process->status = JobProcesses::STATUS_SLEEPING;
        $process->save(false);

        Yii::$app->db->close();//close db connection on sleep mode.

        $sleep_seconds = $job->sleep;
        if ($sleep_seconds < 1)
            $sleep_seconds = 1;
        sleep((int)$sleep_seconds);
    }

    public function actionStart()
    {
        $job_ids = Jobs::getColumnData('id', []);
        $this->startJobs($job_ids);
        $this->stdout("OK\n");
    }

    public function actionRestart()
    {
        $job_ids = Jobs::getColumnData('id', []);
        $this->stopJobs($job_ids);
        $this->startJobs($job_ids);
        $this->stdout("OK\n");
    }

    public function actionStop()
    {
        $job_ids = Jobs::getColumnData('id', []);
        $this->stopJobs($job_ids);
        $this->stdout("OK\n");
    }

    /**
     * @param array $job_ids
     */
    protected function stopJobs($job_ids)
    {
        foreach ($job_ids as $job_id) {
            Jobs::stopJob($job_id);
        }
    }

    protected function startJobs($job_ids)
    {
        foreach ($job_ids as $job_id) {
            Jobs::startJob($job_id);
        }
    }

    public function actionStatus()
    {
        /* @var $jobs Jobs[] */
        $jobs = Jobs::find()->where([])->orderBy(['id' => SORT_ASC])->all();

        $this->stdout("\nThe following jobs were found:\n\n", Console::BOLD);
        foreach ($jobs as $job) {
            $len = strlen($job->id);
            $status = $job->is_active ? 'Active' : 'Stopped';
            $this->stdout($this->ansiFormat($job->id, Console::FG_YELLOW), Console::BOLD);
            $this->stdout(str_repeat(' ', $len + 4 - strlen($job->id)));
            $this->stdout(Console::wrapText($this->ansiFormat($status, $job->is_active ? Console::FG_GREEN : Console::FG_RED), $len + 4 + 2), Console::BOLD);
            $this->stdout(str_repeat(' ', $len + 4 - strlen($job->id)));
            $this->stdout(Console::wrapText($job->threads . '/' . $job->max_threads . ' threads (R:' . JobProcesses::getTotalRunning($job->id) . ',S:' . JobProcesses::getTotalSleeping($job->id) . ')', $len + 4 + 2));
            $this->stdout(str_repeat(' ', $len + 4 - strlen($job->id)));
            $this->stdout(Jobs::decodeExecutionType($job->execution_type));
            $this->stdout(str_repeat(' ', $len + 4 - strlen($job->id)));
            $this->stdout(Console::wrapText('Last Run: ' . DateUtils::formatToLocalDate($job->last_run, "d/m/Y H:i:s") . ' UTC', $len + 4 + 2));

            $this->stdout("\n");
        }
    }
}
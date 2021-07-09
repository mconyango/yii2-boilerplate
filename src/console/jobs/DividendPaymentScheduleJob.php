<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-02-20 22:28
 * Time: 22:28
 */

namespace console\jobs;


use backend\modules\saving\models\DividendPaymentSchedule;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class DividendPaymentScheduleJob extends BaseObject implements JobInterface
{
    use JobTrait;
    /**
     * @var int
     */
    public $modelId;

    /**
     * @var null|string
     */
    public $scenario = null;

    /**
     * @var DividendPaymentSchedule
     */
    private $_model;

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->setModel();
            if ($this->scenario === DividendPaymentSchedule::SCENARIO_POST) {
                $this->_model->postTransactions();
            } else {
                $this->_model->process();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::error($e->getTrace());
        }
    }

    protected function setModel()
    {
        $this->_model = DividendPaymentSchedule::loadModel($this->modelId);
    }
}
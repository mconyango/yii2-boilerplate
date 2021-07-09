<?php

namespace console\jobs;


use backend\modules\loan\models\LoanAccount;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class LoanInterestAccrualJob extends BaseObject implements JobInterface
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
     * @var LoanAccount
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
            $installments = $this->_model->repaymentSchedule;
            foreach ($installments as $installment){
                $installment->createInterestAccrual();
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
        $this->_model = LoanAccount::loadModel($this->modelId);
    }
}
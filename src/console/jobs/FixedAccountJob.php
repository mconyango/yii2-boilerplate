<?php

namespace console\jobs;


use backend\modules\payment\models\DepositTransaction;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class FixedAccountJob extends BaseObject implements JobInterface
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
     * @var DepositTransaction
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
            $this->_model->createFixDepositEarningBreakdown();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::error($e->getTrace());
        }
    }

    protected function setModel()
    {
        $this->_model = DepositTransaction::loadModel($this->modelId);
    }
}
<?php

namespace console\jobs;


use backend\modules\saving\models\AutomatedInternalTransfers;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class AutomatedInternalTransferJob extends BaseObject implements JobInterface
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
     * @var AutomatedInternalTransfers
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
            $this->_model->createTransferBreakdown();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::error($e->getTrace());
        }
    }

    protected function setModel()
    {
        $this->_model = AutomatedInternalTransfers::loadModel($this->modelId);
    }
}
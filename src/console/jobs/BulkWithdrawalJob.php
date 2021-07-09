<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-02-13 21:37
 * Time: 21:37
 */

namespace console\jobs;


use backend\modules\saving\models\BulkWithdrawal;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class BulkWithdrawalJob extends BaseObject implements JobInterface
{
    use JobTrait;

    /**
     * @var int
     */
    public $modelId;

    /**
     * @var bool
     */
    public $lumpsum;

    /**
     * @var BulkWithdrawal
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
            $this->_model->process($this->lumpsum);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::error($e->getTraceAsString());
        }
    }

    protected function setModel()
    {
        $this->_model = BulkWithdrawal::loadModel($this->modelId);
    }
}
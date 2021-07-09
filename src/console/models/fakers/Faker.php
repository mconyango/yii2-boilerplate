<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-23 6:28 PM
 */

namespace console\models\fakers;


use common\models\ActiveRecord;
use Yii;
use yii\base\BaseObject;

class Faker extends BaseObject
{
    const MIN_RECORDS = 1;
    const MAX_RECORDS = 1000000;

    /**
     * @var int
     */
    public $totalRecords;

    /**
     * @var int
     */
    private $_insertBatchSize = 1000;

    /**
     * @var FakerInterface|ActiveRecord
     */
    public $model;

    public function init()
    {
        parent::init();
    }


    /**
     * @param FakerInterface $model
     * @param int $totalRecords
     * @param array $conf
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public static function getInstance(FakerInterface $model, $totalRecords = 1000, $conf = [])
    {
        if (!is_array($conf)) {
            $conf = (array)$conf;
        }
        $conf['class'] = static::class;
        /* @var $instance $this */
        $instance = Yii::createObject($conf);
        $instance->model = $model;
        if (!empty($totalRecords)) {
            $instance->totalRecords = $totalRecords;
        }

        if ($instance->totalRecords < self::MIN_RECORDS) {
            $instance->totalRecords = self::MIN_RECORDS;
        }
        if ($instance->totalRecords > self::MAX_RECORDS) {
            $instance->totalRecords = self::MAX_RECORDS;
        }

        return $instance;
    }

    /**
     * @param bool $activeRecord
     * @return bool
     * @throws \yii\db\Exception
     */
    public function run($activeRecord = true)
    {
        $batchIterations = $this->getInsertBatchIterations();
        for ($b = 0; $b < $batchIterations; $b++) {
            $insertData = [];
            for ($i = ($b * $this->_insertBatchSize); $i < (($b + 1) * $this->_insertBatchSize); $i++) {
                if ($i >= $this->totalRecords) {
                    break;
                }
                $attributes = $this->model->getFakerInsertRow($i);
                if (empty($attributes)) {
                    return false;
                }
                if ($activeRecord) {
                    $new_model = clone $this->model;
                    $new_model->attributes = $attributes;
                    $new_model->save(false);
                } else {
                    $insertData[] = $attributes;
                }

                $consoleMsg = strtr("Table {table}: Processed record {n}\n", [
                    '{table}' => $this->model::getCleanTableName(),
                    '{n}' => $i + 1,
                ]);

                Yii::$app->controller->stdout($consoleMsg);
            }

            $this->model->insertMultiple($insertData);
        }
    }


    /**
     * @return int
     */
    protected function getInsertBatchIterations()
    {
        return (int)ceil($this->totalRecords / $this->_insertBatchSize);
    }
}
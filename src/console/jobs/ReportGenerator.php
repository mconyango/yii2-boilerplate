<?php

namespace console\jobs;


use backend\modules\reports\models\AdhocReport;
use common\helpers\FileManager;
use common\helpers\Lang;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;

class ReportGenerator extends BaseObject implements JobInterface
{
    /**
     * @var int
     */
    public $queueId;

    /**
     * @var array
     */
    private $_jsonArr;

    /**
     * @var array
     */
    private $_errors;

    /**
     * @var string
     */
    private $_sql;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $filepath;

    /**
     * @var yii\db\Query
     */
    private $_query;

    /**
     * @var AdhocReport
     */
    private $_model;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue)
    {
        $this->_model = AdhocReport::find()->andWhere(['id' => $this->queueId])->one();
        if ($this->_model === null) {
            return false;
        }

        try {
            $this->_model->status = AdhocReport::STATUS_PROCESSING;
            $this->_model->save(false);
            $json = json_decode($this->_model->options, true);
            $this->_jsonArr = $json;
            $this->_sql = $this->_model->raw_sql;
            $date_created = \common\helpers\DateUtils::formatToLocalDate($this->_model->created_at);
            $this->filename = $this->_model->name . '_' . $date_created . '_' . time();
            $this->fetchData();

            //ReportNotification::createManualNotifications(ReportNotification::NOTIF_REPORT_COMPLETION, $this->_model->id);
        } catch (\Exception $e) {
            $this->_model->status = AdhocReport::STATUS_ERROR;
            $this->_model->status_remarks = 'An error occurred';
            $this->_model->error_message = $e->getMessage();
            $this->_model->error_trace = $e->getTraceAsString();
            $this->_model->save(false);
            Yii::error($e->getMessage());
        }
    }

    /**
     * @param mixed $params
     * @return mixed
     */
    public static function push($params)
    {
        try {
            /* @var $queue \yii\queue\cli\Queue */
            $queue = Yii::$app->queue;
            if ($params instanceof self) {
                $obj = $params;
            } else {
                $obj = new self($params);
            }

            $id = $queue->push($obj);

            return $id;
        } catch (\Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    protected function fetchData(){
        $connection = Yii::$app->getDb();
        $options = $this->_jsonArr;
        $decodeFields = $options['decodeFields'] ?? [];
        $fieldAliasMapping = $options['fieldAliasMapping'] ?? [];
        $rowTransformer = $options['rowTransformer'] ?? '';
        try {
            //$connection->setQueryBuilder();
            $command = $connection->createCommand($this->_sql);
            $reader = $command->query();
            $count = $reader->rowCount;
            $rows = [];
            $columns = [];
            if ($count > 0) {
                $this->createDataCSV();
                while ($row = $reader->read()) {
                    // we want to replace value of column with the decoded value
                    if($rowTransformer != ''){
                        $row = $rowTransformer($row, $options);
                    }
                    if(count($decodeFields)){
                        try {
                            foreach ($decodeFields as $field => $rules){
                                // check if field to decode is in generated mapping
                                if(array_key_exists($field, $fieldAliasMapping)){
                                    $columnAlias = $fieldAliasMapping[$field]; // this is the column as it appears in the $row
                                    // get the decoded value of this field, e.g main_breed in Animal class
                                    $function = $rules['function'];
                                    $decodedValue = null;
                                    // check if there are params, params must be in the order that they should be called
                                    if(count($rules['params'])){
                                        // check if param is fieldValue, if so, we pass the value of this column in each $row
                                        $params = [];
                                        foreach ($rules['params'] as $r_param){
                                            $param = $r_param;
                                            if($r_param == 'fieldValue'){
                                                $fieldValue = $row[$columnAlias]; // get value we want to decode from $row
                                                $param = $fieldValue;
                                            }
                                            $params[] = $param;
                                        }
                                        $decodedValue = $function(...$params);
                                    }else {
                                        $decodedValue = $function();
                                    }
                                    if ($decodedValue !== null){
                                        $row[$columnAlias] = $decodedValue;
                                    }
                                }
                            }
                        }
                        catch (\Exception $e) {
                            Yii::$app->controller->stdout("{$e->getMessage()} \n");
                            Yii::debug($e->getMessage());
                        }

                    }
                    $rows[] = $row;
                }
                $first = $rows[0];
                $columns = array_keys($first);
                /*
                $batch = 500;
                $batches = [];
                if ($count <= $batch) {
                    $batches = [$rows];
                } else {
                    $batches = array_chunk($rows, $batch);
                }
                */
                $this->populateCSV($columns, $rows);
            }
            else {
                $this->_model->status = AdhocReport::STATUS_COMPLETED;
                $this->_model->status_remarks = Lang::t('No data returned from query');
                $this->_model->save(false);
            }

        }catch (\Exception $e) {
            $this->_model->status = AdhocReport::STATUS_ERROR;
            $this->_model->status_remarks = 'An error occurred';
            $this->_model->error_message = $e->getMessage();
            $this->_model->error_trace = $e->getTraceAsString();
            $this->_model->save(false);
            Yii::$app->controller->stdout("{$e->getMessage()} \n");
        }

    }
    protected function createDataCSV()
    {
        $fileName = $this->filename. '.csv';
        $this->filepath = $this->getBaseDir() . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function populateCSV($columns, $rows){
        $filepath = $this->filepath;
        try {
            $data = [];
            $header = $columns;
            $data[] = $header;
            foreach ($rows as $n => $row) {
                $data[] = $row;
            }

            $fp = fopen($filepath, 'wb');

            foreach ($data as $fields) {
                fputcsv($fp, $fields);
            }

            fclose($fp);

            $this->_model->report_file = $this->filename. '.csv';
            $this->_model->status = AdhocReport::STATUS_COMPLETED;
            $this->_model->status_remarks = '';
            $this->_model->error_message = '';
            $this->_model->error_trace = '';
            $this->_model->save(false);
        } catch (\Exception $e) {
            Yii::$app->controller->stdout("{$e->getMessage()} \n");
        }
    }

    public function getBaseDir()
    {
        return FileManager::createDir(FileManager::getUploadsDir() . DIRECTORY_SEPARATOR . 'adhoc-reports');
    }

    public function getFilePath()
    {
        $path = null;
        if (empty($this->filename))
            return null;

        $file = $this->getBaseDir() . DIRECTORY_SEPARATOR . $this->filename;
        if (file_exists($file)) {
            $path = $file;
        }

        return $path;
    }

}
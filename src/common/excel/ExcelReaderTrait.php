<?php

namespace common\excel;

use common\helpers\FileManager;
use common\helpers\Lang;
use common\helpers\Utils;
use Yii;
use yii\base\Exception;

/**
 * Description of MyExcelProcessor
 * @author Fred <mconyango@gmail.com>
 */
trait ExcelReaderTrait
{

    /**
     *
     * @var string
     */
    public $file;
    /**
     * @var string
     */
    public $original_file_name;

    /**
     *
     * @var string
     */
    public $sheet;

    /**
     *
     * @var integer
     */
    public $start_row = 1;

    /**
     *
     * @var integer
     */
    public $end_row;

    /**
     *
     * @var string
     */
    public $start_column = 'A';

    /**
     *
     * @var string
     */
    public $end_column = 'J';

    /**
     *
     * @var array
     */
    public $placeholder_columns;

    /**
     *
     * @var array
     */
    public $file_columns = ['[phone]' => 'Phone'];

    /**
     *
     * @var array
     */
    private $required_columns = ['phone'];

    /**
     *
     * @var array
     */
    public $placeholders;


    /**
     * Delete file after processing
     * @var
     */
    public $delete_file = true;

    /**
     * @var
     */
    public $created_by;

    /**
     *
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $_objSpreadsheet;

    /**
     *
     * @var \PhpOffice\PhpSpreadsheet\Reader\IReader
     */
    private $_objReader;

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function setObjReader()
    {
        $input_file_type = PHPExcelHelper::getFileType($this->file);
        $this->_objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($input_file_type);
        $this->_objReader->setReadDataOnly(true);
        //$this->objReader->setDelimiter($this->csv_delimiter);
        $this->_objReader->setLoadSheetsOnly($this->sheet);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function setObjSpreadsheet()
    {
        if (empty($this->_objReader))
            $this->setObjReader();

        $this->_objSpreadsheet = $this->_objReader->load($this->file);
    }


    /**
     * @throws Exception
     */
    public function saveFile()
    {
        $this->original_file_name = basename($this->file);
        $temp_path = $this->file;
        $this->file = time() . '_' . $this->original_file_name;
        $new_path = static::getDir() . DIRECTORY_SEPARATOR . $this->file;
        if (copy($temp_path, $new_path))
            FileManager::deleteDir(dirname($temp_path));
        else
            throw new Exception('Could not copy the file to the new location.');
    }

    /**
     * @return string
     */
    public static function getBaseDir()
    {
        return FileManager::getTempDir();
    }

    /**
     * @return string
     */
    public static function getDir()
    {
        return static::getBaseDir();
    }

    /**
     * @return int
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getTotalRows()
    {
        if (empty($this->_objSpreadsheet))
            $this->setObjSpreadsheet();

        return $this->_objSpreadsheet->getActiveSheet()->getHighestRow();
    }

    /**
     * Set preview data
     * @return array|bool
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getPreviewData()
    {
        if (!$this->validate(['file', 'start_row', 'end_row', 'start_column', 'end_column']))
            return false;

        $this->setObjReader();
        //$this->recipients_count = $this->getRecipientsCount();
        $chunkFilter = $this->getChunkFilter();
        $sheetData = $this->getSheetData($chunkFilter, 1);
        if (isset($sheetData[$this->start_row]))
            $data = $sheetData[$this->start_row];
        else
            $data = static::getColumnRange($this->start_column, $this->end_column);
        $this->setPlaceholderColumns($data);

        return $data;
    }

    /**
     * @param array $data
     */
    public function setPlaceholderColumns($data)
    {
        $columns = array_keys($this->file_columns);
        foreach ($data as $k => $v) {
            if (empty($this->placeholder_columns[$k])) {
                $i = Utils::lettersToNum($k) - 1;
                $this->placeholder_columns[$k] = isset($columns[$i]) ? $columns[$i] : null;
            }
        }
    }

    /**
     *
     */
    public function setPlaceholders()
    {
        $this->placeholders = !empty($this->placeholder_columns) ? array_flip($this->placeholder_columns) : null;
    }

    /**
     * @param array $sheetData
     * @return array
     */
    protected function getInsertBatches($sheetData)
    {
        $insert_batch = 500;
        if (count($sheetData) <= $insert_batch)
            $insert_batches = [$sheetData];
        else
            $insert_batches = array_chunk($sheetData, $insert_batch);

        return $insert_batches;
    }

    /**
     *
     * @return PHPExcelChunkReadFilter
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getChunkFilter()
    {
        if (empty($this->_objReader))
            $this->setObjReader();

        if (empty($this->end_row)) {
            $this->end_row = $this->getTotalRows();
        }

        $chunkFilter = new PHPExcelChunkReadFilter(static::getColumnRange($this->start_column, $this->end_column));
        $this->_objReader->setReadFilter($chunkFilter);

        return $chunkFilter;
    }

    /**
     * @param PHPExcelChunkReadFilter $chunkFilter
     * @param integer $chunkSize
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getSheetData($chunkFilter, $chunkSize)
    {
        $chunkFilter->setRows($this->start_row, $chunkSize);
        $this->setObjSpreadsheet();
        $sheetData = $this->_objSpreadsheet->getActiveSheet()->toArray(null, true, true, true);

        return $sheetData;
    }

    /**
     * @return array
     */
    public function getFileColumns()
    {
        $columns = [];
        foreach ($this->file_columns as $k => $v) {
            $columns[$k] = Lang::t($v);
        }
        return $columns;
    }

    /**
     * preview action
     * @return string|bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function previewAction()
    {
        if ($this->load(Yii::$app->request->post())) {
            if ($data = $this->getPreviewData()) {
                $html = Yii::$app->controller->renderAjax('@common/excel/views/excelColumns', ['model' => $this, 'data' => $data, 'columns' => array_merge(['' => ''], $this->getFileColumns())]);
                $response = ['html' => $html, 'success' => true];
                return json_encode($response);
            } else {
                return json_encode(['success' => false]);
            }
        }
        return false;
    }

    /**
     * @param $sheetData
     * @param int $max_insert_batch
     * @return array
     */
    protected function getExcelInsertBatches($sheetData, $max_insert_batch = 500)
    {
        if (count($sheetData) <= $max_insert_batch)
            $insert_batches = [$sheetData];
        else
            $insert_batches = array_chunk($sheetData, $max_insert_batch);

        return $insert_batches;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function processExcelFile()
    {
        $this->setFile();
        $this->setPlaceholders();
        $chunkFilter = $this->getChunkFilter();
        $batch_size = 1000;
        for ($this->start_row; $this->start_row <= $this->end_row; $this->start_row += $batch_size) {
            $sheetData = $this->getSheetData($chunkFilter, $batch_size);
            foreach ($this->getExcelInsertBatches($sheetData) as $batch) {
                $this->processExcelBatchData($batch);
            }
            if (count($sheetData) < $batch_size)
                break;
        }

        if ($this->delete_file)
            @unlink($this->file);
    }

    public function excelValidationRules()
    {
        return [
            [['file', 'start_row', 'start_column', 'end_column'], 'required'],
            [['start_row', 'end_row'], 'number', 'min' => 1],
            [['sheet', 'placeholder_columns', 'placeholders', 'original_file_name', 'delete_file'], 'safe'],
        ];
    }

    /**
     * Declares excel attribute labels.
     */
    public function excelAttributeLabels()
    {
        return [
            'file' => Lang::t('File'),
            'start_row' => Lang::t('Start Row'),
            'end_row' => Lang::t('End Row'),
            'start_column' => Lang::t('Start Column'),
            'end_column' => Lang::t('End Column'),
        ];
    }

    /**
     * Get columns from excel row
     * @param array $excel_row
     * @param array $columns
     * @return array|bool
     */
    public function getExcelRowColumns($excel_row, $columns = [])
    {
        $i = 1;
        foreach ($this->file_columns as $k => $v) {
            $column_name = isset($this->placeholders[$k]) ? $this->placeholders[$k] : Utils::numToLetter($i, true);
            $column = str_replace(['[', ']'], '', $k);
            $columns[$column] = isset($excel_row[$column_name]) ? trim($excel_row[$column_name]) : null;
            $i++;
        }

        if (!empty($this->required_columns)) {
            foreach ($this->required_columns as $c) {
                if (is_null($columns[$c]))
                    return false;
            }
        }

        return $columns;
    }

    public function addToExcelQueue()
    {
        $this->saveFile();
        return true;
        //return ImportExcelQueue::model()->addToQueue($this);
    }

    public function saveExcelData()
    {
        $this->processExcelFile();
    }

    public function setFile()
    {
        $this->file = $this->getDir() . DIRECTORY_SEPARATOR . $this->file;
    }

    /**
     * @param string $start_column
     * @param string $end_column
     * @return array
     */
    public static function getColumnRange($start_column, $end_column)
    {
        $columns = [];
        foreach (PHPExcelHelper::excelColumnRange($start_column, $end_column) as $value) {
            $columns[] = $value;
        }

        return $columns;
    }
}
<?php
namespace common\excel;
/**
 * Description of PHPExcelHelper
 *
 * @author Fred <mconyango@gmail.com>
 */
class PHPExcelChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{

    private $_startRow = 0;
    private $_endRow = 0;
    private $_columns = [];

    /**
     *
     * @param array $columns
     */
    public function __construct($columns)
    {
        $this->_columns = $columns;
    }

    /**
     * Set the list of rows that we want to read
     * @param int $startRow
     * @param int $chunkSize
     */
    public function setRows($startRow, $chunkSize)
    {
        $this->_startRow = $startRow;
        $this->_endRow = $startRow + $chunkSize;
    }

    /**
     *
     * @param array|string $column
     * @param int $row
     * @param string $worksheetName
     * @return boolean
     */
    public function readCell($column, $row, $worksheetName = '')
    {
        //  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
        if (($row >= $this->_startRow && $row < $this->_endRow)) {
            if (in_array($column, $this->_columns)) {
                return true;
            }
        }
        return false;
    }

}

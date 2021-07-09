<?php

namespace common\excel;

/**
 * Description of MyPHPExcelHelper
 *
 * @author Fred <mconyango@gmail.com>
 */
class PHPExcelHelper
{

    /**
     *
     * @param string $file
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function getSheetNames($file)
    {
        $file_type = self::getFileType($file);
        $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
        $objReader->setReadDataOnly(true);
        $chunkFilter = new PHPExcelChunkReadFilter(range('A', 'D'));
        $objReader->setReadFilter($chunkFilter);
        $chunkFilter->setRows(1, 2);
        $objPHPExcel = $objReader->load($file);
        return $objPHPExcel->getSheetNames();
    }


    /**
     *
     * @param string $file
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function getFileType($file)
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);
    }

    /**
     * @param string $lower
     * @param string $upper
     * @return \Generator
     */
    public static function excelColumnRange($lower, $upper)
    {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }

}

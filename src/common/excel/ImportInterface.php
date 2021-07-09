<?php

namespace common\excel;
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 7/23/15
 * Time: 3:16 PM
 */
interface ImportInterface
{
    /**
     * @return boolean
     */
    public function addToExcelQueue();

    /**
     * @return mixed
     */
    public function saveExcelData();

    /**
     * @param $batch
     * @return mixed
     */
    public function processExcelBatchData($batch);
}
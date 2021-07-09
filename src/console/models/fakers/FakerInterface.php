<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-23 7:16 PM
 */

namespace console\models\fakers;


interface FakerInterface
{
    /**
     * @param int $rowNumber
     * @return array
     */
    public function getFakerInsertRow($rowNumber);
}
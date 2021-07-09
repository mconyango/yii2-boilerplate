<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-07-08
 * Time: 13:33
 */

namespace console\jobs;


interface JobInterface extends \yii\queue\JobInterface
{
    /**
     * @param mixed $params
     * @return mixed
     */
    public static function push($params);
}
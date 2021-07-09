<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2019-02-20 22:30
 * Time: 22:30
 */

namespace console\jobs;


use Yii;

trait JobTrait
{
    /**
     * @param mixed $params
     * @return mixed
     */
    public static function push($params)
    {
        /* @var $queue \yii\queue\cli\Queue */
        $queue = Yii::$app->queue;
        if ($params instanceof static) {
            $obj = $params;
        } else {
            $obj = new static($params);
        }

        $id = $queue->push($obj);

        return $id;
    }
}
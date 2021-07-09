<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/04
 * Time: 11:06 AM
 */

namespace api\actions;


use yii\data\ActiveDataProvider;
use yii\rest\IndexAction;

class Index extends IndexAction
{
    /**
     * @return ActiveDataProvider
     */
    public function run()
    {
        return parent::run();
    }
}
<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2016/02/04
 * Time: 11:05 AM
 */

namespace api\actions;


use yii\rest\ViewAction;

class View extends ViewAction
{
    /**
     * @param string $id
     * @return \yii\db\ActiveRecordInterface
     */
    public function run($id)
    {
        return parent::run($id);
    }

}
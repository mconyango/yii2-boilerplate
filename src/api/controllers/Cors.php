<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-03 12:26 PM
 */

namespace api\controllers;


class Cors extends \yii\filters\Cors
{
    public function prepareHeaders($requestHeaders)
    {
        $responseHeaders = parent::prepareHeaders($requestHeaders);
        if (isset($this->cors['Access-Control-Allow-Headers'])) {
            $responseHeaders['Access-Control-Allow-Headers'] = implode(', ',
                $this->cors['Access-Control-Allow-Headers']);
        }

        if (isset($this->cors['Access-Control-Allow-Methods'])) {
            $responseHeaders['Access-Control-Allow-Methods'] = implode(', ',
                $this->cors['Access-Control-Allow-Methods']);
        }

        if (isset($this->cors['Access-Control-Allow-Origin'])) {
            $responseHeaders['Access-Control-Allow-Origin'] = implode(', ', $this->cors['Access-Control-Allow-Origin']);
        }

        return $responseHeaders;
    }

}
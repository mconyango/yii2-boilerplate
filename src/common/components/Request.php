<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/09
 * Time: 8:54 PM
 */

namespace common\components;


class Request extends \yii\web\Request
{
    public $web;
    public $adminUrl;

    public function getBaseUrl()
    {
        return parent::getBaseUrl();
    }

    public function getActualBaseUrl()
    {
        return str_replace($this->adminUrl, "", parent::getBaseUrl());
    }
}
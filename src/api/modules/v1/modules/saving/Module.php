<?php

namespace api\modules\v1\modules\saving;

use api\controllers\Cors;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'api\modules\v1\modules\saving\controllers';

    public function init()
    {
        parent::init();
    }

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => [
                    'Access-Control-Allow-Methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                    'Access-Control-Allow-Headers' => ['Content-Type', 'Cache-Control', 'Pragma', 'Authorization', 'X-Requested-With', 'accept', 'Origin', 'Access-Control-Request-Method', 'Access-Control-Request-Headers'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Origin' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    // Allow OPTIONS caching
                    'Access-Control-Max-Age' => 3600,
                    // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                    'Access-Control-Expose-Headers' => ['Access-Control-Allow-Origin','X-Pagination-Current-Page'],
                ],

            ],
        ];
    }
}

<?php

return [
    'db' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db.php'),
    'assetManager' => [
        'linkAssets' => true,
        'forceCopy' => YII_DEBUG ? true : false,
    ],
    'cache' => [
        'class' => yii\caching\FileCache::class,
    ],
    'urlManager' => [
        'class' => yii\web\UrlManager::class,
        'enablePrettyUrl' => true,
        'showScriptName' => false,
    ],
    'session' => [
        'class' => yii\web\DbSession::class,
        'sessionTable' => '{{sys_app_session}}',
        'cookieParams' => ['httponly' => true, 'lifetime' => 3600 * 4],
        'timeout' => 3600 * 4,
        'useCookies' => true,
        'name' => 'SACCOHUBBACKENDSESSIONID',
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => yii\log\DbTarget::class,
                'levels' => ['error'],
                'logTable' => '{{%conf_log}}',
                'enabled' => false,
            ],
            [
                'class' => yii\log\FileTarget::class,
                'levels' => ['error', 'warning', 'info'],
                'except' => ['yii\db*', 'yii\web\Session*'],
                'fileMode' => 0664,
                'logVars' => [],
            ],
        ],
    ],
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => yii\i18n\PhpMessageSource::class,
                'basePath' => '@common/translations',
                'sourceLanguage' => 'en',
            ],
            'yii' => [
                'class' => yii\i18n\PhpMessageSource::class,
                'basePath' => '@common/translations',
                'sourceLanguage' => 'en'
            ],
            'yii2mod.settings' => [
                'class' => \yii\i18n\PhpMessageSource::class,
                'basePath' => '@yii2mod/settings/messages',
            ],
        ],
    ],
    'formatter' => [
        'class' => \yii\i18n\Formatter::class,
        'dateFormat' => 'php:d-M-Y',
        'datetimeFormat' => 'php:d-M-Y H:i:s',
        'timeFormat' => 'php:H:i:s',
    ],
    'settings' => [
        'class' => \common\components\Settings::class,
    ],
    'localTime' => [
        'class' => \common\components\LocalTime::class,
    ],
    'queue' => [
        'class' => \yii\queue\db\Queue::class,
        'db' => 'db',
        'tableName' => '{{%sys_queue}}',
        'channel' => 'default',
        'mutex' => \yii\mutex\MysqlMutex::class, // Mutex used to sync queries
        //'class' => \yii\queue\amqp_interop\Queue::class,
        //'port' => 5672,
        //'user' => 'guest',
        //'password' => 'guest',
        //'queueName' => 'dwg-queue',
        //'driver' => yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_LIB,
    ],

];
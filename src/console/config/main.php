<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'cache' => [
            'class' => yii\caching\FileCache::class,
            'defaultDuration' => 10,
        ],
        'db' => require(__DIR__ . '/../../common/config/db.php'),
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'except' => ['yii\db*'],
                    'logVars' => [],
                ],
            ],
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'baseUrl' => BASE_URL,
            //'hostInfo' => '',
        ],
    ],
    'controllerMap' => [
        'migration' => [
            'class' => \bizley\migration\controllers\MigrationController::class,
        ],
    ],
    'params' => $params,
];
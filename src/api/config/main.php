<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'default/index',
    'controllerNamespace' => 'api\modules\v1\controllers',
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'class' => \api\modules\v1\Module::class
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => api\modules\v1\models\User::class,
            'enableAutoLogin' => false,
            'loginUrl' => null,
            'enableSession' => false,
        ],
        'request' => [
            'parsers' => [
                'application/json' => \yii\web\JsonParser::class,
            ],
            'enableCookieValidation' => false,
        ],
        'response' => [
            'class' => \yii\web\Response::class,
            'format' => \yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                /** @var  $response \yii\web\Response */
                $response = $event->sender;
                // maintaining compatibility
                if (!$response->isSuccessful) {
                    if ($response->data !== null) {
                        $response->data = [
                            'errors' => $response->data,
                            'code' => $response->statusCode,
                        ];
                    }
                }
            },
        ],
        'urlManagerBackend' => [
            'class' => yii\web\UrlManager::class,
            'baseUrl' => '/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => require(__DIR__ . DIRECTORY_SEPARATOR . 'urlManagerRules.php'),
        ],
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => 'd6492856a4a4c104681de4f3a2e0f9891',
            'jwtValidationData' => \api\components\JwtValidationData::class,
        ],
    ],
    'params' => $params,
    'aliases' => array_merge(
        require(__DIR__ . '/../../backend/config/aliases.php'),
        require(__DIR__ . '/aliases.php')
    ),
];

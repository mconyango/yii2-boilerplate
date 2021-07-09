<?php

return [

    // here you can set theme used for your backend application
    // - template comes with: 'default', 'slate', 'spacelab' and 'cerulean'
    'view' => [
        'theme' => [
            'pathMap' => ['@app/views' => '@webroot/themes/backend/views'],
            'baseUrl' => '@web/themes/backend',
            'basePath' => '@webroot/themes/backend',
        ],
    ],
    'user' => [
        'class' => \common\components\User::class,
        'identityClass' => \backend\modules\auth\models\Users::class,
        'enableAutoLogin' => true,
        'autoRenewCookie' => true,
        'loginUrl' => ['auth/auth/login'],
        'authTimeout' => 60 * 60,
    ],
    'errorHandler' => [
        'errorAction' => 'error/index',
    ],
    'request' => [
        'enableCookieValidation' => true,
        'enableCsrfValidation' => true,
        'cookieValidationKey' => 'DhkVRexpSUIeBA1uQg+ibVkubJ0msDOUdjToAjNZvXc=',
    ],
];
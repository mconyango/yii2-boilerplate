<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-04-08 1:00 PM
 */
return [
    [
        'class' => \yii\rest\UrlRule::class,
        'pluralize' => false,
        'controller' => [
            'auth' => 'v1/auth',
        ],
        'extraPatterns' => [
            'POST login' => 'login',
            //'POST change-password' => 'change-password',
            //'POST reset-password/begin' => 'begin-reset-password',
            //'POST reset-password/finish' => 'complete-reset-password',
            //'POST reset-password/finish/{token}' => 'complete-reset-password',
            'GET activation-code' => 'activation-code',
            'POST new-password' => 'new-password',
            'OPTIONS <action>' => 'options',
        ],
    ]
];
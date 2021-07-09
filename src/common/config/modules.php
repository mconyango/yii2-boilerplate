<?php
/**
 * @author Fred <mconyango@gmail.com>
 * Date: 2015/12/02
 * Time: 4:56 PM
 */
$modules = [
    'gridview' => [
        'class' => kartik\grid\Module::class,
        // enter optional module parameters below - only if you need to
        // use your own export download action or custom translation
        // message source
        //'downloadAction' => 'gridview/export/download',
        // 'i18n' => []
    ],
    'settings' => [
        'class' => \yii2mod\settings\Module::class,
    ],
    'auth' => [
        'class' => \backend\modules\auth\Module::class,
    ],
    'conf' => [
        'class' => \backend\modules\conf\Module::class,
    ],
    'dashboard' => [
        'class' => \backend\modules\dashboard\Module::class,
    ],
];

if (YII_ENV_DEV) {
    return array_merge($modules, [
        'gii' => [
            'class' => yii\gii\Module::class,
        ],
        'debug' => [
            'class' => yii\debug\Module::class,
        ],
    ]);
}
return $modules;

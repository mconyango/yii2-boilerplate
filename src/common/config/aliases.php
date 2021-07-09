<?php

return [
    '@backend' => dirname(dirname(__DIR__)) . '/backend',
    '@common' => dirname(__DIR__),
    '@api' => dirname(dirname(__DIR__)) . '/api',
    '@console' => dirname(dirname(__DIR__)) . '/console',
    '@appRoot' => '/' . basename(dirname(dirname(dirname(__DIR__)))),
    '@uploads' => dirname(dirname(dirname(__DIR__))) . '/uploads',
    '@commonAssets' => '@common/assets',
    '@bower' => '@vendor/bower-asset',
    '@npm' => '@vendor/npm-asset',
];
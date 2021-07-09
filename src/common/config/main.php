<?php
return [
    'name' => 'SACCOHUB',
    //'language' => 'sr',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => require(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'modules.php'),
    'timeZone' => 'UTC',
    'components' => require(__DIR__ . DIRECTORY_SEPARATOR . 'components.php'), // components
    // aliases
    'aliases' => require(__DIR__ . DIRECTORY_SEPARATOR . 'aliases.php'),
];

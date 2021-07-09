<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/params.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'defaultRoute' => 'dashboard/default/index',
    'controllerNamespace' => 'backend\controllers',
    // bootstrapped components
    'bootstrap' => require(__DIR__ . '/bootstrap.php'),

    // modules
    'modules' => [],

    // backend app components
    'components' => require(__DIR__ . DIRECTORY_SEPARATOR . 'components.php'),

    // aliases for the backend. requires aliases defined in aliases.php in the common folder
    'aliases' => require(__DIR__ . DIRECTORY_SEPARATOR . 'aliases.php'),

    'params' => $params,
];

<?php

require(__DIR__ . '/../env.php');
require(__DIR__ . '/../src/vendor/autoload.php');
require(__DIR__ . '/../src/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../src/common/config/bootstrap.php');
require(__DIR__ . '/../src/api/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../src/common/config/main.php'),
    require(__DIR__ . '/../src/api/config/main.php')
);

$application = new yii\web\Application($config);
$application->run();
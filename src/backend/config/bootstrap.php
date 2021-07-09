<?php

$bootstrap = [
    'log',
];
if(YII_ENV_DEV){
    return array_merge($bootstrap, ['debug']);
}
return $bootstrap;
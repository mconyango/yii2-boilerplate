<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-06-23 7:46 PM
 */
return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;port=3306;dbname=saccohub',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8mb4',
    'on afterOpen' => function ($event) {
        $event->sender->createCommand("SET time_zone = '+00:00'")->execute();
    }
];
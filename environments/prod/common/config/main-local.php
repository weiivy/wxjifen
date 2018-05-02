<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=jifen',
            'username' => 'root',
            'password' => 'zz515',
            'charset' => 'utf8mb4',
        ],

        'cache_c' => [
            'class'    => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port'     => '6379',
            'database' => 0,
        ],

        'redis_shared' => [
            'class'    => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port'     => '6379',
            'database' => 2,
        ],

//        'log'=>array(
//            'class'=>'CLogRouter',
//            'routes'=>array(
//                array(
//                    'class'=>'CFileLogRoute',
//                    'levels'=>'trace, info error, warning', //日志标准增加trace, info
//                ),
//            ),
//        ),
    ],
];

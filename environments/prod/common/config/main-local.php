<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=zj_lltsfo2013',
            'username' => 'root',
            'password' => '123123',
            'charset' => 'utf8mb4',
        ],

        'db_slave' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=lltsfo2013',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],

        'db_woqu' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=woqu2017',
            'username' => 'root',
            'password' => '111111',
            'charset' => 'utf8mb4',
        ],

        'db_ip' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=dbip',
            'username' => 'root',
            'password' => '123123',
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

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
        //这个帐号 目前只有001可以访问
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
        //这个帐号需serena帐号才能更新数据结构
        'db_wxadmin' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=wxadmin',
            'username' => 'root',
            'password' => '',
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
        'db_logs' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=llt2017logs',
            'username' => 'root',
            'password' => '123123',
            'charset' => 'utf8mb4',
        ],
        'db_lltmdc' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=lltmdc',
            'username' => 'root',
            'password' => '123123',
            'charset' => 'utf8mb4',
        ],

//        'mailer' => [
//            'class' => 'yii\swiftmailer\Mailer',
//            'class' => 'common\components\Mail',
//            'viewPath' => '@common/mail',
//            'useFileTransport' => false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
//            'transport' => [
//                'class' => 'Swift_SmtpTransport',
//                'host'  => '10.8.10.3',
//                'username' => '',
//                'password' => '',
//                'port' => '25',
//                'encryption' => '',
//            ],
//            'messageConfig'=>[
//                'charset'=>'UTF-8',
//                'from'=>['webmaster@lulutrip.com'=>'Lulutrip.com'],
//                'replyTo' => ['info@lulutrip.com' => 'Lulutrip.com']
//            ],
//            'backup' => true,
//            'recordLog' => true,
//        ],
    ],
];

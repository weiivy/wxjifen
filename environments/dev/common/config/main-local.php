<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=127.0.0.1; dbname=jifen',
            'username' => 'root',
            'password' => '$&@Zz515',
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

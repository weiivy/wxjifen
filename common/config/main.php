<?php
$main = [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'curl' => [
            'class' => 'linslin\yii2\curl\Curl',
        ],
        'mailer' => [
//            'class' => 'yii\swiftmailer\Mailer',
            'class' => 'common\components\Mail',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SendmailTransport',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['1032909502@qq.com'=>'1032909502@qq.com'],
                'replyTo' => ['1032909502@qq.com' => '1032909502@qq.com']
            ],
            'backup' => true,
            'recordLog' => true, //true记录邮件发送日志
        ],
        'cache' => [
            'class'    => 'common\components\Cache',
            'config'   => 'cache_c',
        ],

        'helper' => [
            'class' => 'common\components\Helper',
        ],
        'smsApi'    => [
            'class' => 'common\components\SmsApi'
        ],
    ],
];
$log = include('log.php');
$main['components'] = array_merge($main['components'], $log);

return $main;
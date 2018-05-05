<?php
return [
    'log' => [
        'targets' => [
            'file_error' => [
//                'class' => 'yii\log\FileTarget',
                'class' => 'common\components\FileTargets',
                'levels' => ['error'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                        $iPaddress = gethostname();
                        return "[$iPaddress]";
                    },
                'logFile' => '@runtime/logs/error.log',
                'maxLogFiles' => 3,
                'maxFileSize' => 1024,
            ],
            'file_warning' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['warning'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                        $iPaddress = gethostname();
                        return "[$iPaddress]";
                    },
                'logFile' => '@runtime/logs/warning.log',
                'fileType' => 1,
                'maxLogFiles' => 3,
                'maxFileSize' => 1024,
            ],
            'file_info' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['info'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                        $iPaddress = gethostname();
                        return "[$iPaddress]";
                    },
                'logFile' => '@runtime/logs/info.log',
                'fileType' => 1,
                'maxLogFiles' => 3,
                'maxFileSize' => 1024,
            ],
//            'email' => [
//                'class' => 'common\components\EmailTargets',
//                'levels' => ['error'],
////                'except' => [
////                    'yii\web\HttpException:404',
////                ],
//                'logVars' =>['_SERVER'],
//                //***改为自己对应邮箱名称前缀
//                'message' => [
//                    'to' => 'ivy.zhang@ipptravel.com',
//                    'subject' => 'ivy.local Application Log'
//                ],
//                'prefix' => function ($message) {
//                    $iPaddress = gethostname();
//                    return "[$iPaddress]";
//                },
//            ],
        ],
    ],
];
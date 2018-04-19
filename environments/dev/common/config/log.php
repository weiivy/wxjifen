<?php

return [
    'log' => [
        'targets' => [
	    'file_test' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['info'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
                'categories' => [
                    'api\library\cruise\*',
                ],
                'logFile' => '@runtime/logs/cruise/info.log',
                'fileType' => 1,
                'maxLogFiles' => 1,
                'maxFileSize' => 102400,
            ],
            'file_error' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['error'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
		        'except' => [
                    'yii\db\*',
                    'yii\web\HttpException:404',
                ],
                'logFile' => '@runtime/logs/error/error.log',
                'fileType' => 1,
                'maxLogFiles' => 23,
                'maxFileSize' => 102400,
	        ],
            'file_error_db' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['error'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
                'categories' => [
                    'yii\db\*',
                ],
                'logFile' => '@runtime/logs/error/error_db.log',
                'fileType' => 2,
                'maxLogFiles' => 5,
                'maxFileSize' => 102400,
            ],
	        'file_error_404' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['error'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
                'categories' => [
                    'yii\web\HttpException:404',
                ],
                'logFile' => '@runtime/logs/error/error_404.log',
                'fileType' => 1,
                'maxLogFiles' => 5,
                'maxFileSize' => 102400,
            ],
            'file_warning' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['warning'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
                'logFile' => '@runtime/logs/warning/warning.log',
                'fileType' => 1,
                'maxLogFiles' => 30,
                'maxFileSize' => 102400,
            ],
            'file_info' => [
                'class' => 'common\components\FileTargets',
                'levels' => ['info'],
                'logVars' =>['_SERVER'],
                'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
                'logFile' => '@runtime/logs/info/info.log',
                'fileType' => 1,
                'maxLogFiles' => 30,
                'maxFileSize' => 102400,
            ],
	],
    ],
];

return [
    'log' => [
        'targets' => [
            'file' => [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning', 'info'],
                'logVars' =>['_SERVER'],
		'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
            ],
            'email' => [
                'class' => 'common\components\EmailTargets',
		'levels' => ['error'],
                'except' => [
                    'yii\web\HttpException:404',
                ],
                'logVars' =>['_SERVER'],
                'message' => [
                    'to' => 'serena.liu@ipptravel.com',
                    'subject' => 'product Application Log'
                ],
		'prefix' => function ($message) {
                    $iPaddress = gethostname();
                    return "[$iPaddress]";
                },
            ],
        ],
    ],
];



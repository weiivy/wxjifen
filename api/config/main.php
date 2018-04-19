<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'timeZone'=>'America/Los_Angeles',
    'modules' => [
        'woqu' => [
            'class' => 'api\modules\woqu\Module',
        ],

        'special' => [
            'class' => 'api\modules\special\Module',
        ],
        'admin' => [
            'class' => 'api\modules\admin\Module',
        ],
        'channel' => [
            'class' => 'api\modules\channel\Module',
        ],
        'customized' => [
            'class' => 'api\modules\customized\Module',
        ],
        'rentcar' => [
            'class' => 'api\modules\rentcar\Module',
        ],
        'order' => [
            'class' => 'api\modules\order\Module',
        ],
        'setting' => [
            'class' => 'api\modules\setting\Module',

        ],
        'op' => [
            'class' => 'api\modules\op\Module',
        ],
        'saleactivity' => [
            'class' => 'api\modules\saleactivity\Module',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'cookieValidationKey' => 'nnjpnGAZ3X-N-LaXhZJ5JpNZ_tpfy3sT'
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => require dirname(dirname(__DIR__)) . '/api/config/url_rule.php',
        ],
        'i18n' => [
            'translations' => [
                'api*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@api/messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'api' => 'main.php'
                    ],
                ],
            ],
        ],
    ],
    'params' => $params,
];


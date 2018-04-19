<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

defined('YII_DEBUG') or define('YII_DEBUG', true);
require(__DIR__ . '/../../common/config/define.php');

defined('DOMAIN_BASE') or define("DOMAIN_BASE", "lulu.com");
defined('DOMAIN_NAME') or define("DOMAIN_NAME", ".lulu.com");

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

$application = new yii\web\Application($config);
$application->run();

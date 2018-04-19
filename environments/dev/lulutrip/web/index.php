<?php
error_reporting(E_ERROR);
ini_set('display_errors', 'On');

date_default_timezone_set("America/Los_Angeles");
defined('YII_DEBUG') or define('YII_DEBUG', true);
require(__DIR__ . '/../../common/config/define.php');

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

//session共享
//ini_set("session.cookie_domain", DOMAIN_NAME);
//session_id($_COOKIE['session_id']);
session_start();
$application = new yii\web\Application($config);
$application->run();

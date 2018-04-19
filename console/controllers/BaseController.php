<?php
/**
 * BaseController
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace console\controllers;

use yii\console\Controller;
use Yii;

class BaseController extends Controller
{
    public $startTime;
    public $endTime;
    public $emailTitle;

    /**
     * 检查lock, 写log
     * @author Serena Liu<serena@ipptravel.com>
     * @copyright 2017-10-11
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        $this->startTime = Yii::$app->helper->getMicroTime();
        $fileName = $this->id . '#' . $this->action->id;
        $logDir = Yii::$app->runtimePath . '/logs/base';
        @mkdir($logDir);

        $lockFile = $logDir . '/' . $fileName . '.lock';
        $logFile = $logDir . '/' . $fileName . '.log';

        if(is_file($lockFile))
        {
            die('该脚本正在执行中...');
        }
        else
        {
            file_put_contents($lockFile, date('Y-m-d H:i:s'));
            file_put_contents($logFile, date('Y-m-d H:i:s') . "\r\n");
        }
        return parent::beforeAction($action);
    }

    /**
     * 写log, 删lock
     * @author Serena Liu<serena@ipptravel.com>
     * @copyright 2017-10-11
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        $this->endTime = Yii::$app->helper->getMicroTime();
        $fileName = $this->id . '#' . $this->action->id;
        $logDir = Yii::$app->runtimePath . '/logs/base';

        $lockFile = $logDir . '/' . $fileName . '.lock';
        $fp = fopen($logDir . '/' . $fileName . '.log', 'a+');

        $text = " 时间：" . date('Y-m-d H:i:s') . " 用时：" . ($this->endTime - $this->startTime) . "<br>\r\n";

        if(is_file($lockFile))
        {
            unlink($lockFile);
        }
        fwrite($fp, $text);

        fclose($fp);
        return parent::afterAction($action, $result);
    }

}
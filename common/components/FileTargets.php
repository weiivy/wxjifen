<?php
/**
 * @copyright (c) 2017, lulutrip.com
 * @author  Serena Liu<serena.liu@ipptravel.com>
 */

namespace common\components;

use yii\log\FileTarget;
use yii;
use yii\helpers\FileHelper;

class FileTargets extends FileTarget
{
    /**
     * @var 日志是以日期文件夹区分还是以日期下标开头，默认为日期下标拼接.
     */
    public $fileType = 0;
    public function init()
    {
        parent::init();
        if ($this->logFile === null) {
            $this->logFile = Yii::$app->getRuntimePath() . '/logs/app.log';
        } else {
            /**
             * 对日志进行日期文件夹管理 开始
             */
            $arr = explode('/', $this->logFile);

            $logfileName = $arr[count($arr)-1];
            if($this->fileType == 0){ //日期格式开头拼接
                $this->logFile = str_replace($logfileName, date('Y-m-d').'_'.$logfileName, $this->logFile);
            }else{ //日期文件夹区分
                $dir = str_replace($logfileName, date('Y-m-d'), $this->logFile);
                if(!file_exists($dir)) mkdir ($dir,0777,true);
                $this->logFile = $dir.'/'.$logfileName;
            }
            unset($logfileName, $arr);
            /**
             * 对日志进行日期文件夹管理 结束
             */
            $this->logFile = Yii::getAlias($this->logFile);
        }
        $logPath = dirname($this->logFile);
        if (!is_dir($logPath)) {
            FileHelper::createDirectory($logPath, $this->dirMode, true);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }
}
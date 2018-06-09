<?php

/**
 * 帮助信息
 * @copyright (c) 2017, lulutrip.com
 * @author  Martin Ren<martin@lulutrip.com>
 */

namespace api\library;

use api\models\base\PhoneAreaCode;
use common\models\Cities;
use common\models\ExchangeRate;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class Help extends \yii\base\Component
{
    /**
     * 隐藏手机号中间四位
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     * @param $mobile
     * @return mixed
     */
    public static function fmtMobile($mobile)
    {
        return substr_replace($mobile,'****',3,4);
    }

    /**
     * 递归建立文件夹
     * @author abei
     */
    public static function RecursiveMkdir($path){
        if (!file_exists($path)) {
            self::RecursiveMkdir(dirname($path));
            mkdir(\Yii::$app->getBasePath() . $path, 0777, true);
        }
    }

    public static function createItemPath($type = 'data',$ext='xls'){
        $year = date('Y');
        $day = date('md');
        $n = \Yii::$app->security->generateRandomString(32).'.'.$ext;
        $save_path = "{$type}/{$year}/{$day}";

        $path = $save_path;
        self::RecursiveMkdir($path);
        return array(
            'save_path'=>$path. '/' . $n,
            'web_path'=>$save_path. '/' . $n
        );
    }

    /**
     * 生成全局唯一标识符，类似 09315E33-480F-8635-E780-7A8E61FB49AA
     * @param null $namespace
     * @return string
     */
    public static function guid($namespace = null)
    {
        static $guid = '';
        $uid = uniqid(mt_rand(), true);

        $data = $namespace;
        $data .= isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();                 // 请求那一刻的时间戳
        $data .= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : rand(0, 999999);  // 访问者操作系统信息
        $data .= isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : rand(0, 999999);          // 服务器IP
        $data .= isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : rand(0, 999999);          // 服务器端口号
        $data .= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : rand(0, 999999);          // 远程IP
        $data .= isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : rand(0, 999999);          // 远程端口

        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);

        return $guid;
    }
}
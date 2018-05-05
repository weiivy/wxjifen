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

        $path = '/uploads/'.$save_path;
        self::RecursiveMkdir($path);
        return array(
            'save_path'=>$path. '/' . $n,
            'web_path'=>$save_path. '/' . $n
        );
    }
}
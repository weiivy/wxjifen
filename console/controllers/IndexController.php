<?php
/**
 * 公共部分
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace console\controllers;

use yii\console\Controller;

class IndexController extends Controller
{

    public function actions()
    {
        return [
            //test
            'index'  => [
                'class' => 'console\actions\index\Index',
            ],
            //脚本监控
            'monitor'  => [
                'class' => 'console\actions\index\Monitor',
            ],
        ];
    }

}
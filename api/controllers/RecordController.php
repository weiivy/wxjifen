<?php
/**
 * Created by PhpStorm.
 * User: zhangweiwei
 * Date: 18/4/16
 * Time: 下午10:41
 */

namespace api\controllers;


use yii\rest\Controller;

class RecordController extends Controller
{
    public function actions()
    {
        return [
            'add-order' => 'api\actions\order\AddOrder',
        ];
    }
}
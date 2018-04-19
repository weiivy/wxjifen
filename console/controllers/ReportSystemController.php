<?php

namespace console\controllers;

use yii\console\Controller;

/**
 * 处理订单关联表和主订单表product_type 字段
 * @package console\controllers
 * @copyright (c) 2018, lulutrip.com
 * @author Xiaopei Dou <xiaopei.dou@ipptravel.com>
 */
class ReportSystemController extends BaseController {
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'orderRelated' => [
                'class' => 'console\actions\reportSystem\OrderRelated',
            ],
        ];
    }
}
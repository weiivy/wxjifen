<?php

namespace console\controllers;

use yii\console\Controller;

/**
 * 执行临时脚本数据
 * @package console\controllers
 * @copyright (c) 2017, lulutrip.com
 * @author Victor Tang<victor.tang@ipptravel.com>
 */
class BatchDataReportController extends Controller {
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'orderReport'  => [
                'class' => 'console\actions\batchDataReport\OrderReport',
            ],
            'orderOptimeReport' => [
                'class' => 'console\actions\batchDataReport\orderOptimeReport',
            ],
        ];
    }
}
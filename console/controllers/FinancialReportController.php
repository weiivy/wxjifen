<?php

namespace console\controllers;

/**
 * 导出财务报表
 * @package console\controllers
 * @copyright (c) 2017, lulutrip.com
 * @author Victor Tang<victor.tang@ipptravel.com>
 */
class FinancialReportController extends BaseController {
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'collection'  => [
                'class' => 'console\actions\financialReport\Collection',
            ],
            'orderReport'  => [
                'class' => 'console\actions\financialReport\OrderReport',
            ],
        ];
    }
}
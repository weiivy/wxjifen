<?php
/**
 * 比价工具脚本
 * @copyright 2017-10-20
 * @author Justin Jia<justin.jia@ipptravel.com>
 */

namespace console\controllers;
use yii\console\Controller;
use console\controllers\BaseController;

class ComparePriceController extends BaseController
{
    public $emailTitle;
    public function actions()
    {
        return [
            // 比价脚本
            'price' => [
                'class' => 'console\actions\comparePrice\Price',
            ],
            // 发送邮件脚本
            'email' => [
                'class' => 'console\actions\comparePrice\Email',
            ],
        ];
    }
}
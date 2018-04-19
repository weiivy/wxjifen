<?php
/**
 * 邮件提醒控制器
 * @copyright (c) 2017, lulutrip.com
 * @author Serena Liu<serena.liu@ipptravel.com>
 */
namespace console\controllers;

class EmailReminderController extends BaseController
{

    public function actions()
    {
        return [
            //llt2013 - emailflightinfonotify 航班提醒
            'flight'  => [
                'class' => 'console\actions\emailReminder\Flight',
            ],
            //llt2013 - emailRateReminder 评价提醒
            'comment' => [
                'class' => 'console\actions\emailReminder\Comment',
            ],
            //llt2013 - etourvoucherReminder 行前提醒
            'itinerary' => [
                'class' => 'console\actions\emailReminder\Itinerary',
            ],
        ];
    }

}
<?php

namespace api\controllers;


use yii\rest\Controller;

/**
 * message
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'wxlogin' => 'api\actions\site\WxLogin',
            'sendSms' => 'api\actions\site\SendSms',
            'bank-config' => 'api\actions\site\GetBankConfig',
            'check-member' => 'api\actions\site\CheckMember',
            'get-mobile' => 'api\actions\site\GetMobile',
            'pay-back' => 'api\actions\site\PayBackMoney',
            'bank-info' => 'api\actions\site\GetBankInfo',
        ];
    }
}
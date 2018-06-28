<?php

namespace api\controllers;


use yii\rest\Controller;

class MemberController extends Controller
{
    public function actions()
    {
        return [
            'register' => 'api\actions\member\Register',
            'login' => 'api\actions\member\Login',
            'get-capital-detail' => 'api\actions\member\GetCapitalDetail',
            'get-orders' => 'api\actions\member\GetOrderList',
            'member' => 'api\actions\member\GetMember',
            'friends' => 'api\actions\member\GetFriends',
            'add-order' => 'api\actions\order\AddOrder',
            'upgrade' => 'api\actions\member\Upgrade',
            'get-money' => 'api\actions\member\GetMoney',
            'upload-avatar' => 'api\actions\member\UpdateImg',
            'sncy-oid' => 'api\actions\member\SncyOId',
            'update-name' => 'api\actions\member\UpdateName',
        ];
    }
}
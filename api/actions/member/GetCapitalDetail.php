<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\library\capitalDetails\CapitalDetailsService;
use api\library\member\MemberService;
use Yii;

/**
 * 交易明细
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetCapitalDetail extends BaseAction
{
    public function run()
    {
        $page = Yii::$app->request->post('page');
        $page = $page > 0 ? $page : 1;
        $pageSize = Yii::$app->request->post('pageSize', 10);
        $kind = Yii::$app->request->post('type');
        $openid = Yii::$app->request->post('openid');
        $memberId = MemberService::getMemberByOpenid($openid);
        return [
            'status' => 200,
            'data'   => CapitalDetailsService::getList($page, $pageSize, $kind, $memberId)
        ];
    }
}
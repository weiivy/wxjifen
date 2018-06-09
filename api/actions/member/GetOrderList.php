<?php


namespace api\actions\member;


use api\actions\BaseAction;
use api\library\member\MemberService;
use api\library\order\OrderService;
use Yii;

/**
 * 获取订单列表
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetOrderList extends BaseAction
{
    public function run()
    {
        $page = Yii::$app->request->post('page');
        $page = $page > 0 ? $page : 1;
        $pageSize = Yii::$app->request->post('pageSize', 10);
        $status = Yii::$app->request->post('status');
        $memberId = $this->memberId;
        return [
            'status' => 200,
            'data'   => OrderService::getList($page, $pageSize, $status, $memberId)
        ];

    }
}
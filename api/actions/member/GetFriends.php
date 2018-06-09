<?php

namespace api\actions\member;

use api\actions\BaseAction;
use api\library\member\MemberService;
use api\models\Member;
use Yii;

/**
 * 好友列表
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetFriends extends BaseAction
{
    public function run()
    {
        $page = Yii::$app->request->post('page');
        $page = $page > 0 ? $page : 1;
        $pageSize = Yii::$app->request->post('pageSize', 10);
        $memberId = $this->memberId;
        return [
            'status' => 200,
            'data'   => MemberService::getFriends($memberId, $page, $pageSize)
        ];

    }
}
<?php

/**
 * api Action 继承类
 * @copyright (c) 2017, lulutrip.com
 * @author  Martin Ren<martin@lulutrip.com>
 */

namespace api\actions;

use api\library\member\MemberService;
use yii\rest\Action;
use Yii;

class BaseAction extends Action
{
    public $modelClass = '';
    public $memberId;

    /**
     * 根据参数获取memberid
     */
    public function init()
    {
        //获取操作人信息
        $memberId = Yii::$app->request->post('memberId');
        $member = MemberService::getMemberInfo($memberId);
        if(!empty($member)){
            $this->memberId = $member->id;
        }
        return [
            'status' => 0,
            'message' => '用户ID错误'
        ];

    }
}
<?php

namespace api\actions\site;


use api\actions\BaseAction;
use api\library\Help;
use api\models\Member;
use Yii;

/**
 * 获取邀请人手机号码
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetMobile extends BaseAction
{
    public function run()
    {
        $memberId = Yii::$app->request->post('memberId');
        $member = Member::findOne(['id' => $memberId]);
        if(empty($member->mobile)) {
            return ['status' => 0, 'data' => []];
        }
        return ['status' => 200, 'data' => ['mobile' => $member->mobile, 'mobileAlisa' => Help::fmtMobile($member->mobile)]];
    }

}
<?php
namespace api\actions\member;

use api\actions\BaseAction;
use api\models\Member;
use Yii;

/**
 * 提现金额
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetMoney extends BaseAction
{
    public function run()
    {
        $memberId = $this->memberId;
        $member = Member::find()->select('money')->where(['id'=> $memberId])->asArray()->one();
        return [
            'status' => 200,
            'data' => number_format($member['money'], 2)
        ];
    }
}
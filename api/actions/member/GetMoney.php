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
        $openId = Yii::$app->request->post('openid');
        $member = Member::find()->select('money')->where(['openid'=> $openId])->asArray()->one();
        return [
            'status' => 200,
            'data' => (float)$member['money']
        ];
    }
}
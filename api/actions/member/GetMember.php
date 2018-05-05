<?php

namespace api\actions\member;


use api\actions\BaseAction;
use api\models\CapitalDetails;
use api\models\Member;
use api\models\Order;
use Yii;

/**
 * 个人中心数据
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class GetMember extends BaseAction
{
    public function run()
    {
        $openid = Yii::$app->request->post('openid');

        //用户信息
        $member = Member::find()->where(['openid' => $openid, 'status' => Member::status_10])->asArray()->one();
        if(empty($member)) return ['status' => 200, 'message' => 'openid错误'];
        $member['grade'] = Member::gradeAlisa($member['grade']);
        $data = [
            'member' => $member
        ];

        //交易记录
        $data['trade'] = (float)$this->getTrade($member['id']);

        //订单数量
        $data['order'] = $this->getOrderCount($member['id']);
        return [
            'status' => 200,
            'data'   => $data
        ];

    }

    /**
     * 会员交易金额
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     * @param $memberId
     * @return mixed
     */
    private function getTrade($memberId)
    {
        //累计收益
        return CapitalDetails::find()
            ->where(['member_id' => $memberId])
            ->andWhere(['kind' => [CapitalDetails::KIND_20, CapitalDetails::KIND_30]])
            ->sum("money");
    }

    /**
     * 订单数量
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     * @param $memberId
     * @return array
     */
    private function getOrderCount($memberId)
    {
        $reviewing = Order::find()->where(['member_id' => $memberId])->andWhere(['status' => Order::STATUS_20])->count();
        $success   = Order::find()->where(['member_id' => $memberId])->andWhere(['status' => Order::STATUS_30])->count();
        $error     = Order::find()->where(['member_id' => $memberId])->andWhere(['status' => Order::STATUS_40])->count();
        return ['reviewingCount' => $reviewing, 'successCount' => $success, 'errorCount' => $error];
    }
}
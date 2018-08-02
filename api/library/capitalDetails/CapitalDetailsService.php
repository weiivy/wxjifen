<?php

namespace api\library\capitalDetails;


use api\library\member\MemberService;
use api\models\BankConfig;
use api\models\CapitalDetails;
use api\models\Member;
use api\models\Order;
use yii\base\Component;
use yii\data\Pagination;

/**
 * 资金明细服务类
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class CapitalDetailsService extends Component
{
    /**
     * 获取资金明细
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $page
     * @param $pageSize
     * @param $type
     * @param $memberId
     * @return array
     */
    public static function getList($page, $pageSize, $type, $memberId)
    {
        $list = CapitalDetails::find();
        $list->where(['member_id'=> $memberId, 'status' => CapitalDetails::STATUS_YES]);
        if(!empty($type)) {
            $list->andWhere(['type' => $type]);
        }
        $list->orderBy('id DESC');
        $pages = new Pagination(['totalCount' =>$list->count(), 'pageSize' => $pageSize]);
        $pages->setPage($page-1);
        $list = $list->offset($pages->offset)->limit($pages->pageSize)->asArray()->all();

        foreach($list as $key => &$value) {
            $value['kind'] = CapitalDetails::kindAlisa($value['kind']);
            $value['created_at'] = date('Y-m-d H:i:s', $value['created_at']);
        }
        return ['list' => $list, 'count' => $pages->totalCount];
    }


    /**
     * 升级返现
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-05-04
     * @param $memberId
     * @param $totalFee
     * @return bool
     */
    public static function upgradeRebate($memberId, $totalFee)
    {
        $addFee = [
            CapitalDetails::FEE_199 => 120,
            CapitalDetails::FEE_998 => 600,
        ];
        //检查该用户是否有父级
        $pid = MemberService::getPid($memberId);
        if( $pid === 0) {
            return true;
        }

        $addFee = floatval($addFee[$totalFee]);
        \Yii::$app->db->beginTransaction();
        //有父级给父级返佣
        $capitalDetails = new CapitalDetails();
        $capitalDetails->member_id = $pid;
        $capitalDetails->from_id = $memberId;
        $capitalDetails->type = "+";
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->kind = CapitalDetails::KIND_30;
        $capitalDetails->money = $addFee;
        $capitalDetails->created_at = $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            \Yii::error(json_encode($capitalDetails->errors));
            \Yii::$app->db->transaction->rollBack();
            return false;
        }

        //修改用户金额
        $member = Member::findOne(['id' => $pid]);
        $oldMoney = floatval($member->money);
        $member->money = floatval($oldMoney+ $addFee);
        $member->updated_at = time();
        $member->save();
        if($member->errors) {
            \Yii::error(json_encode($member->errors));
            \Yii::$app->db->transaction->rollBack();
            return false;
        }
        \Yii::$app->db->transaction->commit();
        return true;
    }

    /**
     * 提成
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-05-04
     * @param $orderId
     * @return bool
     */
    public static function commission($orderId)
    {
        //检查订单是否存在
        $order = Order::findOne(['id' => $orderId]);
        if(empty($order)) return true;

        //检查该用户是否有父级
        $pid = MemberService::getPid($order->member_id);
        if( $pid === 0) {
            return true;
        }

        $currentMember = Member::findOne(['id' => $order->member_id]);
        $topMember = Member::findOne(['id' => $pid]);
        if($currentMember->grade <= $topMember->grade) {
            return true;
        }

        //获取银行配置点数
        $bankConfig = BankConfig::findOne(['bank' => $order->bank]);;
        if(empty($bankConfig)) return true;

        \Yii::$app->db->beginTransaction();
        //有父级给父级提成
        $commission = round((($bankConfig->money / $bankConfig->score) * $order->integral), 2);
        $commission =  round(($bankConfig->money - $commission) * 0.01, 2);

        $capitalDetails = new CapitalDetails();
        $capitalDetails->member_id = $pid;
        $capitalDetails->type = "+";
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->kind = CapitalDetails::KIND_30;
        $capitalDetails->money = $commission;
        $capitalDetails->created_at = $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            \Yii::$app->db->transaction->rollBack();
            \Yii::error(json_encode($capitalDetails->errors));
            return;
        }

        //修改用户金额
        $member = Member::findOne(['id' => $pid]);
        $member->money = $commission;
        $member->updated_at = time();
        $member->save();
        if($member->errors) {
            \Yii::error(json_encode($member->errors));
            \Yii::$app->db->transaction->rollBack();
            return;
        }
        \Yii::$app->db->transaction->commit();
        return true;
    }


}
<?php

namespace api\library\capitalDetails;


use api\library\member\MemberService;
use api\models\BankConfig;
use api\models\CapitalDetails;
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
     * @return bool
     */
    public static function upgradeRebate($memberId)
    {
        //检查该用户是否有父级
        $pid = MemberService::getPid($memberId);
        if( $pid === 0) {
            return true;
        }

        //有父级给父级返佣
        $capitalDetails = new CapitalDetails();
        $capitalDetails->member_id = $pid;
        $capitalDetails->type = "+";
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->kind = CapitalDetails::KIND_30;
        $capitalDetails->money = 120;
        $capitalDetails->created_at = $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            \Yii::error(json_encode($capitalDetails->errors));
            return false;
        }
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

        //获取银行配置点数
        $bankConfig = BankConfig::findOne(['bank' => $order->bank]);;
        if(empty($bankConfig)) return true;

        //有父级给父级提成
        $commission = round((($bankConfig->money / $bankConfig->score) * $order->integral), 2);
        $capitalDetails = new CapitalDetails();
        $capitalDetails->member_id = $pid;
        $capitalDetails->type = "+";
        $capitalDetails->status = CapitalDetails::STATUS_YES;
        $capitalDetails->kind = CapitalDetails::KIND_30;
        $capitalDetails->money = $commission;
        $capitalDetails->created_at = $capitalDetails->updated_at = time();
        $capitalDetails->save();
        if($capitalDetails->errors) {
            \Yii::error(json_encode($capitalDetails->errors));
        }
        return true;
    }


}
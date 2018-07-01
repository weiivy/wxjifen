<?php

namespace api\models;

/**
 * Model CapitalDetails
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class CapitalDetails extends \common\models\CapitalDetails
{
    const KIND_10 = 10; //提现
    const KIND_20 = 20; //提成
    const KIND_30 = 30; //升级返现
    const KIND_31 = 31; //升级
    const KIND_40 = 40; //提现
    const KIND_50 = 50; //兑换

    const STATUS_YES = 1; //支付
    const STATUS_NO = 2;  //待支付

    /**
     * 交易类型别名
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $kind
     * @return mixed|null
     */
    public static function kindAlisa($kind)
    {
        $array = [
            static::KIND_10 => '提现',
            static::KIND_20 => '提成',
            static::KIND_30 => '升级返现',
            static::KIND_31 => '升级',
            static::KIND_40 => '提现',
            static::KIND_50 => '兑换',
        ];
        return isset($array[$kind]) ? $array[$kind] : null;
    }

    /**
     * 充值金额
     * @param $type
     * @return int|mixed
     */
    public static function payFee($type)
    {
        $arr = [
            10 => 199,  //合伙人
            20 => 998   //黄金合伙人
        ];
        return isset($arr[$type]) ? $arr[$type] : 0;
    }


}
<?php

namespace api\models;

/**
 * Model Order
 * @copyright (c) 2018, lulutrip.com
 * @author  Ivy Zhang<ivyzhang@lulutrip.com>
 */
class Order extends \common\models\Order
{
    const STATUS_10 = 10;  // 待审核
    const STATUS_20 = 20;  // 审核中
    const STATUS_30 = 30;  // 审核成功
    const STATUS_40 = 40;  // 审核失败

    /**
     * 订单状态
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-10
     * @param $kind
     * @return mixed|null
     */
    public static function statusAlisa($status)
    {
        $array = [
            static::STATUS_10 => '待审核 ',
            static::STATUS_20 => '审核中',
            static::STATUS_30 => '审核成功',
            static::STATUS_40 => '审核失败'
        ];
        return isset($array[$status]) ? $array[$status] : null;
    }

    /**
     * 银行参数
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     *
     * @return array 返回数据
     */
    public static function bankParams()
    {
        return [
            'JSYH' => '建设银行',
            'GDYH' => '光大银行',
            'ZHYH' => '中国银行',
            'BJYH' => '北京银行',
            'HFYH' => '汇丰银行',
            'ZGYD' => '中国移动',
            'ZGLT' => '中国联通',
        ];
    }

    /**
     * 银行别名
     * @author Ivy Zhang<ivyzhang@lulutrip.com>
     * @copyright 2018-04-13
     * @param $bank
     * @return mixed|null
     */
    public static function bankAlisa($bank)
    {
        $banks = static::bankParams();
        return isset($banks[$bank]) ? $banks[$bank] : null;
    }
}
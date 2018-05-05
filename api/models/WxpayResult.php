<?php
namespace api\models;
use yii\db\ActiveRecord;

/**
 * 微信支付结果
 */
class WxpayResult extends ActiveRecord
{
    public static $table = '{{pay_wxpay_result}}';
}
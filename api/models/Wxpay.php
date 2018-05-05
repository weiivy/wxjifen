<?php
namespace api\models;
use yii\db\ActiveRecord;

/**
 * 微信支付
 */
class Wxpay extends ActiveRecord
{
    public static $table = '{{pay_wxpay}}';

    //支付方式识别字符串
    public static $code = 'wxpay';

    //有效
    const STATUS_VALID = 1;
    //无效
    const STATUS_INVALID = 2;
}
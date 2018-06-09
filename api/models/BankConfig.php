<?php


namespace api\models;


class BankConfig extends \common\models\BankConfig
{
    const TYPE_10 = 10;  //合伙人
    const TYPE_20 = 20;  //代理
    const TYPE_30 = 30;  //股东

    const STATUS_YES = 10;
    const STATUS_NO  = 20;
}